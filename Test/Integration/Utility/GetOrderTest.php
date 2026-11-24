<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Integration\Utility;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Utility\GetOrder;
use Yireo\CheckoutTester2\Utility\OrderToken;

/**
 * @magentoAppArea frontend
 */
class GetOrderTest extends TestCase
{
    private ObjectManagerInterface $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->resetRequestParams();
    }

    protected function tearDown(): void
    {
        $this->resetRequestParams();
        $this->setOrderIdInConfig(0);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsOrderFromUrlWithValidToken()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig($this->getOrderId('100000003'));
        $this->setSignedOrderIdInRequest($this->getOrderId('100000002'));

        $target = $this->getTarget();
        $this->assertSame('100000002', $target->get()->getIncrementId());
        $this->assertSame(GetOrder::SOURCE_URL, $target->getSource());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetIgnoresOrderFromUrlWithoutToken()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig($this->getOrderId('100000004'));
        $this->getRequest()->setParam('order_id', $this->getOrderId('100000001'));

        $target = $this->getTarget();
        $this->assertSame('100000004', $target->get()->getIncrementId());
        $this->assertSame(GetOrder::SOURCE_CONFIG, $target->getSource());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetIgnoresOrderFromUrlWithInvalidToken()
    {
        $this->setCreatedAtOfOrders();
        $orderId = $this->getOrderId('100000001');
        $this->getRequest()->setParams([
            'order_id' => $orderId,
            'expires' => time() + 3600,
            'token' => 'foobar',
        ]);

        $target = $this->getTarget();
        $this->assertSame('100000002', $target->get()->getIncrementId());
        $this->assertSame(GetOrder::SOURCE_LATEST, $target->getSource());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetIgnoresOrderFromUrlWithExpiredToken()
    {
        $this->setCreatedAtOfOrders();
        $this->setSignedOrderIdInRequest($this->getOrderId('100000001'), time() - 1);

        $this->assertSame('100000002', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetIgnoresOrderFromUrlWithTokenOfOtherOrder()
    {
        $this->setCreatedAtOfOrders();
        $this->setSignedOrderIdInRequest($this->getOrderId('100000004'));
        $this->getRequest()->setParam('order_id', $this->getOrderId('100000001'));

        $this->assertSame('100000002', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsOrderFromConfigWithUnknownOrderInUrl()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig($this->getOrderId('100000004'));
        $this->getRequest()->setParam('order_id', 999999999);

        $this->assertSame('100000004', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsLatestOrderWithUnknownOrderInConfig()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig(999999999);

        $target = $this->getTarget();
        $this->assertSame('100000002', $target->get()->getIncrementId());
        $this->assertSame(GetOrder::SOURCE_LATEST, $target->getSource());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsLatestOrderByCreationDate()
    {
        $this->setCreatedAtOfOrders();

        $this->assertSame('100000002', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetSkipsOrdersOfOtherStores()
    {
        $this->setCreatedAtOfOrders();
        $this->setSignedOrderIdInRequest($this->getOrderId('100000003'));

        $this->assertSame('100000002', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetReturnsEmptyOrderWithoutOrders()
    {
        $this->objectManager->get(ResourceConnection::class)->getConnection()->delete(
            $this->objectManager->get(ResourceConnection::class)->getTableName('sales_order')
        );

        $this->getRequest()->setParam('order_id', 999999999);
        $target = $this->getTarget();
        $order = $target->get();
        $this->assertEmpty($order->getEntityId());
        $this->assertSame(GetOrder::SOURCE_NONE, $target->getSource());
    }

    private function getTarget(): GetOrder
    {
        return $this->objectManager->create(GetOrder::class);
    }

    private function getRequest(): RequestInterface
    {
        return $this->objectManager->get(RequestInterface::class);
    }

    private function resetRequestParams(): void
    {
        $this->getRequest()->setParams(['order_id' => null, 'expires' => null, 'token' => null]);
    }

    private function setSignedOrderIdInRequest(int $orderId, ?int $expires = null): void
    {
        $expires = $expires ?? time() + 3600;
        $storeId = (int)$this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $this->getRequest()->setParams([
            'order_id' => $orderId,
            'expires' => $expires,
            'token' => $this->objectManager->get(OrderToken::class)->create($orderId, $storeId, $expires),
        ]);
    }

    private function setOrderIdInConfig(int $orderId): void
    {
        $this->objectManager->get(MutableScopeConfigInterface::class)->setValue(
            'checkouttester2/settings/order_id',
            $orderId,
            ScopeInterface::SCOPE_STORE,
            'default'
        );
    }

    private function getOrderId(string $incrementId): int
    {
        $order = $this->objectManager->create(Order::class);
        $order->loadByIncrementId($incrementId);
        $this->assertNotEmpty($order->getEntityId(), 'Order ' . $incrementId . ' not found');

        return (int)$order->getEntityId();
    }

    /**
     * Make the second order the latest one, to make sure that it is not simply the highest ID being picked
     */
    private function setCreatedAtOfOrders(): void
    {
        $resource = $this->objectManager->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $createdAt = [
            '100000001' => '2020-01-01 00:00:00',
            '100000002' => '2020-01-05 00:00:00',
            '100000003' => '2020-01-03 00:00:00',
            '100000004' => '2020-01-04 00:00:00',
        ];

        foreach ($createdAt as $incrementId => $date) {
            $connection->update(
                $resource->getTableName('sales_order'),
                ['created_at' => $date],
                ['increment_id = ?' => $incrementId]
            );
        }
    }
}
