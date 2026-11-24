<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Integration\Controller\Adminhtml;

use Magento\Config\Model\Config as ConfigModel;
use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Yireo\CheckoutTester2\Block\Field\Link;
use Yireo\CheckoutTester2\Utility\OrderToken;

/**
 * @magentoAppArea adminhtml
 */
class ConfigSectionTest extends AbstractBackendController
{
    public function testConfigSectionIsRendered()
    {
        $this->dispatch('backend/admin/system_config/edit/section/checkouttester2/');

        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('Yireo CheckoutTester', $body);
        $this->assertStringContainsString('checkouttester2_settings_order_id', $body);
        $this->assertStringContainsString('class="yireo-funding"', $body);
        $this->assertStringContainsString('Open success page in new window', $body);
        $this->assertMatchesRegularExpression('#href="[^"]+checkouttester/index/success/?\?___store=default"#', $body);
    }

    public function testIpSettingsAreOnlyShownInDefaultScope()
    {
        $this->dispatch('backend/admin/system_config/edit/section/checkouttester2/');
        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('checkouttester2_settings_ip', $body);
        $this->assertStringContainsString('checkouttester2_settings_trusted_proxies', $body);
    }

    public function testIpSettingsAreNotShownInWebsiteScope()
    {
        $this->dispatch('backend/admin/system_config/edit/section/checkouttester2/website/1/');
        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('checkouttester2_settings_order_id', $body);
        $this->assertStringNotContainsString('checkouttester2_settings_ip', $body);
        $this->assertStringNotContainsString('checkouttester2_settings_trusted_proxies', $body);
    }

    public function testAclResourceIsDefined()
    {
        $acl = $this->_objectManager->get(AclBuilder::class)->getAcl();
        $this->assertTrue($acl->hasResource('Yireo_CheckoutTester2::config'));
    }

    public function testLinkIsOpenedWithoutOpener()
    {
        $this->dispatch('backend/admin/system_config/edit/section/checkouttester2/');
        $this->assertStringContainsString('rel="noopener noreferrer"', (string)$this->getResponse()->getBody());
    }

    public function testFundingLinksAreOpenedWithoutOpener()
    {
        $this->dispatch('backend/admin/system_config/edit/section/checkouttester2/');
        $body = (string)$this->getResponse()->getBody();
        $this->assertMatchesRegularExpression(
            '#<a href="https://github.com/sponsors/yireo"\s+target="_blank"'
            . '\s+rel="noopener noreferrer"\s+title="Opens[^"]+tab">#',
            $body
        );
        $this->assertMatchesRegularExpression(
            '#<a href="https://github.com/sponsors/jissereitsma"\s+target="_blank"'
            . '\s+rel="noopener noreferrer"\s+title="Opens[^"]+tab">#',
            $body
        );
    }

    public function testGetFrontendLink()
    {
        $this->assertStringContainsString('checkouttester/index/success', $this->getLinkBlock()->getFrontendLink());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/web/url/use_store 1
     * @magentoConfigFixture fixture_second_store_store web/url/use_store 1
     */
    public function testGetFrontendLinkUsesStoreFromRequest()
    {
        $this->getRequest()->setParam('store', 'fixture_second_store');

        $link = $this->getLinkBlock()->getFrontendLink();
        $this->assertStringContainsString('/fixture_second_store/checkouttester/index/success', $link);
        $this->assertSame('fixture_second_store', $this->getQuery($link)['___store'] ?? null);
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/web/url/use_store 1
     * @magentoConfigFixture fixture_second_store_store web/url/use_store 1
     */
    public function testGetFrontendLinkUsesDefaultStoreWithoutScope()
    {
        $this->assertDefaultStoreLink($this->getLinkBlock()->getFrontendLink());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/web/url/use_store 1
     * @magentoConfigFixture fixture_second_store_store web/url/use_store 1
     */
    public function testGetFrontendLinkFallsBackToDefaultStoreWithUnknownStore()
    {
        $this->getRequest()->setParam('store', 'foobar');
        $this->assertDefaultStoreLink($this->getLinkBlock()->getFrontendLink());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @magentoConfigFixture default/web/url/use_store 1
     * @magentoConfigFixture fixture_second_store_store web/url/use_store 1
     */
    public function testGetFrontendLinkFallsBackToDefaultStoreWithUnknownWebsite()
    {
        $this->getRequest()->setParam('website', '999999');
        $this->assertDefaultStoreLink($this->getLinkBlock()->getFrontendLink());
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoConfigFixture default/web/url/use_store 1
     * @magentoConfigFixture fixture_second_store_store web/url/use_store 1
     */
    public function testGetFrontendLinkUsesDefaultStoreOfWebsiteFromRequest()
    {
        $website = $this->_objectManager->get(StoreManagerInterface::class)->getWebsite('test');
        $this->getRequest()->setParam('website', (string)$website->getId());

        $link = $this->getLinkBlock()->getFrontendLink();
        $this->assertStringContainsString('/fixture_second_store/checkouttester/index/success', $link);
    }

    public function testGetFrontendLinkWithoutOrderIdHasNoToken()
    {
        $this->getRequest()->setParam('store', 'default');

        $link = $this->getLinkBlock()->getFrontendLink();
        $this->assertStringNotContainsString('order_id', $link);
        $this->assertStringNotContainsString('token', $link);
        $this->assertSame('default', $this->getQuery($link)['___store'] ?? null);
    }

    /**
     * @magentoConfigFixture default_store checkouttester2/settings/order_id 42
     */
    public function testGetFrontendLinkContainsSignedOrderId()
    {
        $this->getRequest()->setParam('store', 'default');

        $link = $this->getLinkBlock()->getFrontendLink();
        $query = [];
        parse_str((string)parse_url($link, PHP_URL_QUERY), $query);

        $this->assertSame('default', $query['___store'] ?? null);
        $this->assertSame('42', $query['order_id'] ?? null);
        $this->assertGreaterThan(time(), (int)($query['expires'] ?? 0));
        $this->assertTrue($this->_objectManager->get(OrderToken::class)->isValid(
            42,
            (int)$this->_objectManager->get(StoreManagerInterface::class)->getStore('default')->getId(),
            (int)$query['expires'],
            (string)($query['token'] ?? '')
        ));
    }

    public function testSavingInvalidIpAddressFails()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"foobar" is not a valid IP address.');
        $this->saveSettings(['ip' => ['value' => '127.0.0.1,foobar']]);
    }

    public function testSavingWildcardWithoutOnlyInDevModeFails()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The wildcard "*" is only allowed');
        $this->saveSettings(['only_in_dev_mode' => ['value' => '0'], 'ip' => ['value' => '*']]);
    }

    private function saveSettings(array $fields): void
    {
        $config = $this->_objectManager->create(ConfigModel::class);
        $config->setSection('checkouttester2');
        $config->setGroups(['settings' => ['fields' => $fields]]);
        $config->save();
    }

    private function getQuery(string $link): array
    {
        $query = [];
        parse_str((string)parse_url($link, PHP_URL_QUERY), $query);

        return $query;
    }

    private function assertDefaultStoreLink(string $link): void
    {
        $this->assertSame('default', $this->getQuery($link)['___store'] ?? null);
        $this->assertStringContainsString('/default/checkouttester/index/success', $link);
        $this->assertStringNotContainsString('/admin/', $link);
    }

    private function getLinkBlock(): Link
    {
        $block = $this->_objectManager->get(LayoutInterface::class)->createBlock(Link::class);
        $this->assertInstanceOf(Link::class, $block);

        return $block;
    }
}
