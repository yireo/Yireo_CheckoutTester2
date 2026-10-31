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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\TestCase\AbstractController as ControllerTestCase;
use Yireo\CheckoutTester2\Exception\ForbiddenAccess;
use Yireo\CheckoutTester2\Exception\InvalidOrderId;
use Laminas\Stdlib\Parameters;

class BrowseTest extends ControllerTestCase
{
    /**
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 0
     */
    public function testIfPageFailsIfDisabled()
    {
        $this->expectException(ForbiddenAccess::class);
        $this->dispatch('/checkouttester/index/success');
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
        $this->expectException(InvalidOrderId::class);
        $this->dispatch('/checkouttester/index/success');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture current_store checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageFailsWithWrongIpSettings()
    {
        $this->expectException(ForbiddenAccess::class);
        $this->getRequest()->setServer(new Parameters(['HTTP_CLIENT_IP' => '1.1.1.2']));
        $this->dispatch('/checkouttester/index/success');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture current_store checkouttester2/settings/enabled 1
     * @magentoConfigFixture current_store checkouttester2/settings/ip 1.1.1.1
     */
    public function testIfPageFailsWithCorrectIpSettings()
    {
        $this->getRequest()->setServer(new Parameters(['HTTP_CLIENT_IP' => '1.1.1.1']));
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
        $this->dispatch('/checkouttester/index/success/order_id/' . $order->getEntityId());

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
    public function testIfOrderIsRegisteredInSession()
    {
        $order = $this->getFixtureOrder();
        $this->dispatch('/checkouttester/index/success');

        $currentOrder = $this->_objectManager->get(Registry::class)->registry('current_order');
        $this->assertInstanceOf(OrderInterface::class, $currentOrder);
        $this->assertSame((int)$order->getEntityId(), (int)$currentOrder->getEntityId());

        $checkoutSession = $this->_objectManager->get(CheckoutSession::class);
        $this->assertSame((int)$order->getEntityId(), (int)$checkoutSession->getLastOrderId());
        $this->assertSame((string)$order->getIncrementId(), (string)$checkoutSession->getLastRealOrderId());
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

    private function getFixtureOrder(): OrderInterface
    {
        $order = $this->_objectManager->create(Order::class);
        $order->loadByIncrementId('100000001');
        $this->assertNotEmpty($order->getEntityId());

        return $order;
    }
}
