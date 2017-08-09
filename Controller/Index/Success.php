<?php
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types = 1);
namespace Yireo\CheckoutTester2\Controller\Index;

/**
 * CheckoutTester frontend controller
 *
 * @category    CheckoutTester2
 * @package Yireo\CheckoutTester2\Controller\Index
 */
class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Yireo\CheckoutTester2\Helper\Data
     */
    protected $moduleHelper;


    /**
     * @var \Yireo\CheckoutTester2\Helper\Order
     */
    protected $orderHelper;

    /**
     * Success constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Yireo\CheckoutTester2\Helper\Data $moduleHelper
     * @param \Yireo\CheckoutTester2\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Yireo\CheckoutTester2\Helper\Data $moduleHelper,
        \Yireo\CheckoutTester2\Helper\Order $orderHelper
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->moduleHelper = $moduleHelper;
        $this->orderHelper = $orderHelper;
    }

    /**
     * Success page action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     *
     * @throws \Yireo\CheckoutTester2\Exception\ForbiddenAccess
     * @throws \Yireo\CheckoutTester2\Exception\InvalidOrderId
     */
    public function execute()
    {
        // Check access
        if ($this->moduleHelper->hasAccess() == false) {
            throw new \Yireo\CheckoutTester2\Exception\ForbiddenAccess('Access denied');
        }

        // Fetch the order
        $order = $this->getOrder();

        // Fail when there is no valid order
        if (!$order->getEntityId()) {
            throw new \Yireo\CheckoutTester2\Exception\InvalidOrderId('Invalid order ID');
        }

        // Register this order
        $this->registerOrder($order);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('checkouttester_index_index');

        return $resultPage;
    }

    /**
     * Method to fetch the current order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrder() : \Magento\Sales\Api\Data\OrderInterface
    {
        $orderIdFromUrl = (int)$this->getRequest()->getParam('order_id');
        $order = $this->orderHelper->getOrderById($orderIdFromUrl);
        if ($order->getEntityId()) {
            return $order;
        }

        $orderIdFromConfig = (int)$this->moduleHelper->getOrderIdFromConfig();
        $order = $this->orderHelper->getOrderById($orderIdFromConfig);
        if ($order->getEntityId()) {
            return $order;
        }

        $lastOrderId = $this->orderHelper->getLastInsertedOrderId();
        $order = $this->orderHelper->getOrderById($lastOrderId);

        if ($order->getEntityId()) {
            return $order;
        }

        return $this->orderHelper->getEmptyOrder();
    }

    /**
     * Method to register the order in this session
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    protected function registerOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        // Register this order as the current order
        $currentOrder = $this->registry->registry('current_order');
        if (empty($currentOrder)) {
            $this->registry->register('current_order', $order);
        }

        // Load the session with this order
        $this->checkoutSession->setLastOrderId($order->getEntityId())
            ->setLastRealOrderId($order->getIncrementId());

        // Optionally dispatch an event
        $this->dispatchEvents($order);
    }

    /**
     * Method to optionally dispatch order-related events
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function dispatchEvents(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        if ($this->moduleHelper->allowDispatchCheckoutOnepageControllerSuccessAction()) {
            $this->_eventManager->dispatch('checkout_onepage_controller_success_action', array('order_ids' => array($order->getEntityId())));
        }
    }
}