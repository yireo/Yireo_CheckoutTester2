<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Integration\Controller\Adminhtml;

use Magento\Framework\Acl\Builder as AclBuilder;
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
        $this->assertMatchesRegularExpression('#href="[^"]+checkouttester/index/success/?"#', $body);
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
    }

    public function testGetFrontendLinkWithoutOrderIdHasNoToken()
    {
        $this->getRequest()->setParam('store', 'default');

        $link = $this->getLinkBlock()->getFrontendLink();
        $this->assertStringNotContainsString('order_id', $link);
        $this->assertStringNotContainsString('token', $link);
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

        $this->assertSame('42', $query['order_id'] ?? null);
        $this->assertGreaterThan(time(), (int)($query['expires'] ?? 0));
        $this->assertTrue($this->_objectManager->get(OrderToken::class)->isValid(
            42,
            (int)$this->_objectManager->get(StoreManagerInterface::class)->getStore('default')->getId(),
            (int)$query['expires'],
            (string)($query['token'] ?? '')
        ));
    }

    private function getLinkBlock(): Link
    {
        $block = $this->_objectManager->get(LayoutInterface::class)->createBlock(Link::class);
        $this->assertInstanceOf(Link::class, $block);

        return $block;
    }
}
