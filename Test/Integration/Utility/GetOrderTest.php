<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Integration\Utility;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Utility\GetOrder;

/**
 * @magentoAppArea frontend
 */
class GetOrderTest extends TestCase
{
    private ObjectManagerInterface $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    protected function tearDown(): void
    {
        $this->getRequest()->setParams([]);
        $this->setOrderIdInConfig(0);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsOrderFromUrl()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig($this->getOrderId('100000003'));
        $this->getRequest()->setParam('order_id', $this->getOrderId('100000002'));

        $this->assertSame('100000002', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsOrderFromConfigWithUnknownOrderInUrl()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig($this->getOrderId('100000003'));
        $this->getRequest()->setParam('order_id', 999999999);

        $this->assertSame('100000003', $this->getTarget()->get()->getIncrementId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_list.php
     */
    public function testGetReturnsLatestOrderWithUnknownOrderInConfig()
    {
        $this->setCreatedAtOfOrders();
        $this->setOrderIdInConfig(999999999);

        $this->assertSame('100000002', $this->getTarget()->get()->getIncrementId());
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
     * @magentoDbIsolation enabled
     */
    public function testGetReturnsEmptyOrderWithoutOrders()
    {
        $this->objectManager->get(ResourceConnection::class)->getConnection()->delete(
            $this->objectManager->get(ResourceConnection::class)->getTableName('sales_order')
        );

        $this->getRequest()->setParam('order_id', 999999999);
        $order = $this->getTarget()->get();
        $this->assertEmpty($order->getEntityId());
    }

    private function getTarget(): GetOrder
    {
        return $this->objectManager->create(GetOrder::class);
    }

    private function getRequest(): RequestInterface
    {
        return $this->objectManager->get(RequestInterface::class);
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
