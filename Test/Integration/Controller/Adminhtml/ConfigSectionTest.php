<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Integration\Controller\Adminhtml;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Yireo\CheckoutTester2\Block\Field\Link;

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

    private function getLinkBlock(): Link
    {
        $block = $this->_objectManager->get(LayoutInterface::class)->createBlock(Link::class);
        $this->assertInstanceOf(Link::class, $block);

        return $block;
    }
}
