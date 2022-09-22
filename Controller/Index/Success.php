<?php declare(strict_types=1);
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Controller\Index;

use Magento\Backend\Model\View\Result\Page;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Yireo\CheckoutTester2\Config\Config as ModuleConfig;
use Yireo\CheckoutTester2\Exception\ForbiddenAccess;
use Yireo\CheckoutTester2\Exception\InvalidOrderId;
use Yireo\CheckoutTester2\Utility\Access;
use Yireo\CheckoutTester2\Utility\GetOrder;

class Success implements HttpGetActionInterface
{
    private PageFactory $resultPageFactory;
    private Registry $registry;
    private Session $checkoutSession;
    private ModuleConfig $moduleConfig;
    private Access $access;
    private GetOrder $getOrder;
    private EventManager $eventManager;

    /**
     * Success constructor.
     *
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Session $checkoutSession
     * @param ModuleConfig $moduleConfig
     * @param Access $access
     * @param GetOrder $getOrder
     * @param EventManager $eventManager
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Registry $registry,
        Session $checkoutSession,
        ModuleConfig $moduleConfig,
        Access $access,
        GetOrder $getOrder,
        EventManager $eventManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->moduleConfig = $moduleConfig;
        $this->access = $access;
        $this->getOrder = $getOrder;
        $this->eventManager = $eventManager;
    }

    /**
     * Success page action
     *
     * @return Page
     *
     * @throws ForbiddenAccess
     * @throws InvalidOrderId
     */
    public function execute()
    {
        if ($this->moduleConfig->enabled() === false) {
            throw new ForbiddenAccess('Module is disabled');
        }

        if ($this->access->hasAccess() === false) {
            throw new ForbiddenAccess('Access denied for IP ' . $this->access->getCurrentIpAddress());
        }

        $order = $this->getOrder->get();

        if (!$order->getEntityId()) {
            throw new InvalidOrderId('Invalid order ID');
        }

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('checkouttester_index_index');

        $this->registerOrder($order);

        return $resultPage;
    }

    /**
     * Method to register the order in this session
     *
     * @param OrderInterface $order
     */
    protected function registerOrder(OrderInterface $order)
    {
        $currentOrder = $this->registry->registry('current_order');
        if (empty($currentOrder)) {
            $this->registry->register('current_order', $order);
        }

        $this->checkoutSession->setLastOrderId($order->getEntityId())
            ->setLastRealOrderId($order->getIncrementId());

        $this->dispatchEvents($order);
    }

    /**
     * Method to optionally dispatch order-related events
     *
     * @param OrderInterface $order
     */
    public function dispatchEvents(OrderInterface $order)
    {
        if (!$this->moduleConfig->allowDispatchCheckoutOnepageControllerSuccessAction()) {
            return;
        }

        $eventData = ['order_ids' => [$order->getEntityId()]];
        $this->eventManager->dispatch('checkout_onepage_controller_success_action', $eventData);
    }
}
