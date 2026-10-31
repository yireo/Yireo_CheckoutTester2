<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Unit\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Config\Config;
use Yireo\CheckoutTester2\Controller\Index\Success as Target;
use Yireo\CheckoutTester2\Exception\ForbiddenAccess;
use Yireo\CheckoutTester2\Exception\InvalidOrderId;
use Yireo\CheckoutTester2\Utility\Access;
use Yireo\CheckoutTester2\Utility\GetOrder;

class SuccessTest extends TestCase
{
    private Config&MockObject $config;
    private Access&MockObject $access;
    private GetOrder&MockObject $getOrder;
    private EventManager&MockObject $eventManager;
    private PageFactory&MockObject $pageFactory;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->access = $this->createMock(Access::class);
        $this->getOrder = $this->createMock(GetOrder::class);
        $this->eventManager = $this->createMock(EventManager::class);
        $this->pageFactory = $this->createMock(PageFactory::class);
    }

    public function testExecuteFailsWhenDisabled()
    {
        $this->config->method('enabled')->willReturn(false);
        $this->access->expects($this->never())->method('hasAccess');
        $this->pageFactory->expects($this->never())->method('create');

        $this->expectException(ForbiddenAccess::class);
        $this->expectExceptionMessage('Module is disabled');
        $this->getTargetObject()->execute();
    }

    public function testExecuteFailsWithoutAccess()
    {
        $this->config->method('enabled')->willReturn(true);
        $this->access->method('hasAccess')->willReturn(false);
        $this->access->method('getCurrentIpAddress')->willReturn('1.2.3.4');
        $this->getOrder->expects($this->never())->method('get');

        $this->expectException(ForbiddenAccess::class);
        $this->expectExceptionMessage('Access denied for IP 1.2.3.4');
        $this->getTargetObject()->execute();
    }

    public function testExecuteFailsWithoutValidOrder()
    {
        $this->config->method('enabled')->willReturn(true);
        $this->access->method('hasAccess')->willReturn(true);
        $this->getOrder->method('get')->willReturn($this->getOrderMock(null));
        $this->pageFactory->expects($this->never())->method('create');

        $this->expectException(InvalidOrderId::class);
        $this->getTargetObject()->execute();
    }

    public function testDispatchEventsWhenAllowed()
    {
        $order = $this->getOrderMock(42);
        $this->config->method('allowDispatchCheckoutOnepageControllerSuccessAction')->willReturn(true);
        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('checkout_onepage_controller_success_action', ['order_ids' => [42], 'order' => $order]);

        $this->getTargetObject()->dispatchEvents($order);
    }

    public function testDispatchEventsWhenNotAllowed()
    {
        $this->config->method('allowDispatchCheckoutOnepageControllerSuccessAction')->willReturn(false);
        $this->eventManager->expects($this->never())->method('dispatch');

        $this->getTargetObject()->dispatchEvents($this->getOrderMock(42));
    }

    private function getOrderMock(?int $entityId): OrderInterface&MockObject
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getEntityId')->willReturn($entityId);

        return $order;
    }

    private function getTargetObject(): Target
    {
        return new Target(
            $this->pageFactory,
            $this->createMock(Registry::class),
            $this->createMock(Session::class),
            $this->config,
            $this->access,
            $this->getOrder,
            $this->eventManager
        );
    }
}
