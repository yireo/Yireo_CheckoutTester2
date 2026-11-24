<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Unit\Controller\Index;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
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
    private RequestInterface&MockObject $request;
    private HttpResponse&MockObject $response;
    private array $sessionData = [];

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->access = $this->createMock(Access::class);
        $this->getOrder = $this->createMock(GetOrder::class);
        $this->eventManager = $this->createMock(EventManager::class);
        $this->pageFactory = $this->createMock(PageFactory::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->response = $this->createMock(HttpResponse::class);
        $this->sessionData = [];
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
        $this->getOrder->expects($this->never())->method('get');

        $this->expectException(ForbiddenAccess::class);
        $this->expectExceptionMessage('Access denied');
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

    public function testExecuteReplacesOrderIdParamWithResolvedOrder()
    {
        $page = $this->createMock(Page::class);
        $this->config->method('enabled')->willReturn(true);
        $this->access->method('hasAccess')->willReturn(true);
        $this->getOrder->method('get')->willReturn($this->getOrderMock(42));
        $this->pageFactory->method('create')->willReturn($page);
        $this->request->expects($this->once())->method('setParams')->with(['order_id' => 42]);
        $page->expects($this->once())->method('renderResult')->with($this->response);

        $this->assertSame($this->response, $this->getTargetObject()->execute());
    }

    public function testExecuteOnlySetsLastOrderInSessionWhileRendering()
    {
        $this->sessionData = ['last_order_id' => 7, 'last_real_order_id' => '000000007'];
        $page = $this->createMock(Page::class);
        $this->config->method('enabled')->willReturn(true);
        $this->access->method('hasAccess')->willReturn(true);
        $this->getOrder->method('get')->willReturn($this->getOrderMock(42, '000000042'));
        $this->pageFactory->method('create')->willReturn($page);

        $renderedSessionData = [];
        $page->method('renderResult')->willReturnCallback(function () use (&$renderedSessionData, $page) {
            $renderedSessionData = $this->sessionData;
            return $page;
        });

        $this->getTargetObject()->execute();

        $this->assertSame(['last_order_id' => 42, 'last_real_order_id' => '000000042'], $renderedSessionData);
        $this->assertSame(['last_order_id' => 7, 'last_real_order_id' => '000000007'], $this->sessionData);
    }

    public function testDispatchEventsWhenAllowed()
    {
        $order = $this->getOrderMock(42);
        $this->config->method('allowDispatchCheckoutOnepageControllerSuccessAction')->willReturn(true);
        $this->getOrder->method('isTrustedSource')->willReturn(true);
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

    public function testDispatchEventsNotWithUntrustedOrderSource()
    {
        $this->config->method('allowDispatchCheckoutOnepageControllerSuccessAction')->willReturn(true);
        $this->getOrder->method('isTrustedSource')->willReturn(false);
        $this->eventManager->expects($this->never())->method('dispatch');

        $this->getTargetObject()->dispatchEvents($this->getOrderMock(42));
    }

    public function testExecuteDoesNotDispatchEventsWithUntrustedOrderSource()
    {
        $page = $this->createMock(Page::class);
        $this->config->method('enabled')->willReturn(true);
        $this->config->method('allowDispatchCheckoutOnepageControllerSuccessAction')->willReturn(true);
        $this->access->method('hasAccess')->willReturn(true);
        $this->getOrder->method('get')->willReturn($this->getOrderMock(42));
        $this->getOrder->method('isTrustedSource')->willReturn(false);
        $this->pageFactory->method('create')->willReturn($page);
        $this->eventManager->expects($this->never())->method('dispatch');

        $this->getTargetObject()->execute();
    }

    public function testExecuteDispatchesEventsWithTrustedOrderSource()
    {
        $page = $this->createMock(Page::class);
        $this->config->method('enabled')->willReturn(true);
        $this->config->method('allowDispatchCheckoutOnepageControllerSuccessAction')->willReturn(true);
        $this->access->method('hasAccess')->willReturn(true);
        $this->getOrder->method('get')->willReturn($this->getOrderMock(42));
        $this->getOrder->method('isTrustedSource')->willReturn(true);
        $this->pageFactory->method('create')->willReturn($page);
        $this->eventManager->expects($this->once())->method('dispatch');

        $this->getTargetObject()->execute();
    }

    private function getOrderMock(?int $entityId, ?string $incrementId = null): OrderInterface&MockObject
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getEntityId')->willReturn($entityId);
        $order->method('getIncrementId')->willReturn($incrementId);

        return $order;
    }

    private function getTargetObject(): Target
    {
        $session = $this->createMock(Session::class);
        $session->method('__call')->willReturnCallback(function (string $method, array $args) use ($session) {
            $keys = ['LastOrderId' => 'last_order_id', 'LastRealOrderId' => 'last_real_order_id'];
            $key = $keys[substr($method, 3)] ?? $method;
            if (str_starts_with($method, 'set')) {
                $this->sessionData[$key] = $args[0] ?? null;
                return $session;
            }

            return $this->sessionData[$key] ?? null;
        });

        return new Target(
            $this->pageFactory,
            $this->createMock(Registry::class),
            $session,
            $this->config,
            $this->access,
            $this->getOrder,
            $this->eventManager,
            $this->request,
            $this->response
        );
    }
}
