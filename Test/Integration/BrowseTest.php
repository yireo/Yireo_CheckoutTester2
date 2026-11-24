<?php declare(strict_types=1);
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_EmailTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Integration;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractController as ControllerTestCase;
use Laminas\Stdlib\Parameters;
use Yireo\CheckoutTester2\Utility\OrderToken;

class BrowseTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setServer(['REMOTE_ADDR' => '127.0.0.1']);
    }

    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIfPageFailsFromPublicIpWithoutConfiguredIp()
    {
        $this->setServer(['REMOTE_ADDR' => '8.8.8.8']);
        $this->dispatch('/checkouttester/index/success');
        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 0
     */
    public function testIfPageFailsIfDisabled()
    {
        $this->dispatch('/checkouttester/index/success');
        $this->assert404NotFound();
    }

    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testIfPageWorksByDefault()
    {
        $this->dispatch('/checkouttester/index/success');
        $body = (string)$this->getResponse()->getBody();
        $this->assertNotEmpty($body);
        $this->assertTrue((bool)strpos($body, 'Thank you for your purchase!'));
    }

    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfPageFailsWithoutValidOrder()
    {
        $this->dispatch('/checkouttester/index/success');
        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture default/checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageFailsWithWrongIpSettings()
    {
        $this->setServer(['REMOTE_ADDR' => '1.1.1.2']);
        $this->dispatch('/checkouttester/index/success');
        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture default/checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageFailsWithSpoofedProxyHeaders()
    {
        $this->setServer([
            'REMOTE_ADDR' => '6.6.6.6',
            'HTTP_CLIENT_IP' => '1.1.1.1',
            'HTTP_X_FORWARDED_FOR' => '1.1.1.1',
        ]);
        $this->dispatch('/checkouttester/index/success');
        $this->assert404NotFound();
        $this->assertStringNotContainsString('Thank you for your purchase!', (string)$this->getResponse()->getBody());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture default/checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageWorksWithCorrectIpSettings()
    {
        $this->setServer(['REMOTE_ADDR' => '1.1.1.1']);
        $this->dispatch('/checkouttester/index/success');
        $body = (string)$this->getResponse()->getBody();
        $this->assertNotEmpty($body);
        $this->assertTrue((bool)strpos($body, 'Thank you for your purchase!'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfPageShowsOrderFromUrl()
    {
        $order = $this->getFixtureOrder();
        $orderId = (int)$order->getEntityId();
        $storeId = (int)$this->_objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $expires = time() + 3600;
        $token = $this->_objectManager->get(OrderToken::class)->create($orderId, $storeId, $expires);
        $this->dispatch('/checkouttester/index/success/order_id/' . $orderId . '/expires/' . $expires . '/token/' . $token);

        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('Thank you for your purchase!', $body);
        $this->assertStringContainsString((string)$order->getIncrementId(), $body);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfPageFallsBackToLatestOrderWithUnknownOrderId()
    {
        $this->dispatch('/checkouttester/index/success/order_id/999999999');

        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('Thank you for your purchase!', $body);
        $this->assertStringContainsString((string)$this->getFixtureOrder()->getIncrementId(), $body);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfOrderIsRegisteredButNotStoredInSession()
    {
        $order = $this->getFixtureOrder();
        $checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $checkoutSession->setLastOrderId(null)->setLastRealOrderId(null);

        $this->dispatch('/checkouttester/index/success');
        $this->assertStringContainsString((string)$order->getIncrementId(), (string)$this->getResponse()->getBody());

        $currentOrder = $this->_objectManager->get(Registry::class)->registry('current_order');
        $this->assertInstanceOf(OrderInterface::class, $currentOrder);
        $this->assertSame((int)$order->getEntityId(), (int)$currentOrder->getEntityId());

        $this->assertEmpty($checkoutSession->getLastOrderId());
        $this->assertEmpty($checkoutSession->getLastRealOrderId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfPageHasTitleAndRobotsMeta()
    {
        $this->dispatch('/checkouttester/index/success');

        $body = (string)$this->getResponse()->getBody();
        $this->assertStringContainsString('<title>CheckoutTester Success Page</title>', $body);
        $this->assertMatchesRegularExpression('/<meta name="robots" content="NOINDEX,NOFOLLOW"/', $body);
        $this->assertMatchesRegularExpression('/<body[^>]+class="[^"]*checkout-onepage-success/', $body);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     */
    public function testIfPageHasNoRegistrationBlockAndIsNotCacheable()
    {
        $this->dispatch('/checkouttester/index/success');
        $this->assertStringContainsString('Thank you for your purchase!', (string)$this->getResponse()->getBody());

        $layout = $this->_objectManager->get(LayoutInterface::class);
        $this->assertContains('checkouttester_index_index', $layout->getUpdate()->getHandles());
        $this->assertFalse($layout->getBlock('checkout.registration'));
        $this->assertNotFalse($layout->getBlock('checkouttester2.nocache'));
        $this->assertFalse($layout->isCacheable());
    }

    private function setServer(array $server): void
    {
        $this->getRequest()->setServer(new Parameters($server));
    }

    private function getFixtureOrder(): OrderInterface
    {
        $order = $this->_objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        $this->assertNotEmpty($order->getEntityId());

        return $order;
    }
}
