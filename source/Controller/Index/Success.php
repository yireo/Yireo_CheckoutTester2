<?php
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

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
     * Success constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Yireo\CheckoutTester2\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Yireo\CheckoutTester2\Helper\Data $moduleHelper
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->registry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Success page action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // Check access
        if ($this->moduleHelper->hasAccess() == false) {
            die('Access denied');
        }

        // Fetch the order
        $order = $this->getOrder();

        // Fail when there is no valid order
        if ($order == false) {
            throw new \Psr\Log\InvalidArgumentException('Invalid order ID');
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
     * @return false|\Magento\Sales\Model\Order
     */
    protected function getOrder()
    {
        $orderIdFromUrl = (int)$this->getRequest()->getParam('order_id');
        $order = $this->loadOrder($orderIdFromUrl);
        if ($order) {
            return $order;
        }

        $orderIdFromConfig = (int)$this->moduleHelper->getOrderIdFromConfig();
        $order = $this->loadOrder($orderIdFromConfig);
        if ($order) {
            return $order;
        }

        $lastOrderId = $this->moduleHelper->getLastInsertedOrderId();
        $order = $this->loadOrder($lastOrderId);
        if ($order) {
            return $order;
        }

        return false;
    }

    /**
     * Method to try to load an order from an unvalidated ID
     *
     * @param int $orderId
     *
     * @return false|\Magento\Sales\Model\Order
     */
    protected function loadOrder($orderId)
    {
        $order = $this->getOrderById($orderId);
        if ($order !== false && $order->getId() > 0) {
            return $order;
        }

        return false;
    }

    /**
     * @param int $orderId
     *
     * @return false|\Magento\Sales\Model\Order
     */
    protected function getOrderById($orderId)
    {
        if (empty($orderId)) {
            return false;
        }

        try {
            return $this->orderRepository->get($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return false;
        }
    }

    /**
     * Method to register the order in this session
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function registerOrder($order)
    {
        // Register this order as the current order
        $currentOrder = $this->registry->registry('current_order');
        if (empty($currentOrder)) {
            $this->registry->register('current_order', $order);
        }

        // Load the session with this order
        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());

        // Optionally dispatch an event
        $this->dispatchEvents($order);
    }

    /**
     * Method to optionally dispatch order-related events
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function dispatchEvents($order)
    {
        if ($this->moduleHelper->allowDispatchCheckoutOnepageControllerSuccessAction()) {
            $this->_eventManager->dispatch('checkout_onepage_controller_success_action', array('order_ids' => array($order->getId())));
        }
    }
}