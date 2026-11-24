<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Utility;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yireo\CheckoutTester2\Config\Config;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;

class GetOrder
{
    public const SOURCE_NONE = 'none';
    public const SOURCE_URL = 'url';
    public const SOURCE_CONFIG = 'config';
    public const SOURCE_LATEST = 'latest';

    private RequestInterface $request;
    private Config $config;
    private OrderFactory $orderFactory;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SortOrderBuilder $sortOrderBuilder;
    private StoreManagerInterface $storeManager;
    private OrderToken $orderToken;
    private string $source = self::SOURCE_NONE;
    private bool $hasUntrustedOrderId = false;

    public function __construct(
        RequestInterface $request,
        Config $config,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StoreManagerInterface $storeManager,
        OrderToken $orderToken
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeManager = $storeManager;
        $this->orderToken = $orderToken;
    }

    /**
     * Method to fetch the current order
     *
     * @return OrderInterface
     */
    public function get(): OrderInterface
    {
        $this->source = self::SOURCE_NONE;
        $this->hasUntrustedOrderId = false;

        $order = $this->getOrderById($this->getOrderIdFromUrl());
        if ($order->getEntityId()) {
            $this->source = self::SOURCE_URL;
            return $order;
        }

        $orderIdFromConfig = $this->config->getOrderIdFromConfig();
        $order = $this->getOrderById($orderIdFromConfig);
        if ($order->getEntityId()) {
            $this->source = self::SOURCE_CONFIG;
            return $order;
        }

        $lastOrderId = $this->getLastInsertedOrderId();
        $order = $this->getOrderById($lastOrderId);

        if ($order->getEntityId()) {
            $this->source = self::SOURCE_LATEST;
            return $order;
        }

        return $this->getEmptyOrder();
    }

    /**
     * Return the source of the order that was returned by the last call to get()
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Check whether the order returned by the last call to get() comes from a trusted source and the request did not
     * contain an order ID without a valid token
     *
     * @return bool
     */
    public function isTrustedSource(): bool
    {
        return $this->source !== self::SOURCE_NONE && $this->hasUntrustedOrderId === false;
    }

    /**
     * Return the order ID from the URL, but only when it is signed with a valid token
     *
     * @return int
     */
    private function getOrderIdFromUrl(): int
    {
        $orderId = (int)$this->request->getParam('order_id');
        if ($orderId < 1) {
            return 0;
        }

        $expires = (int)$this->request->getParam('expires');
        $token = $this->request->getParam('token');
        $storeId = $this->getCurrentStoreId();
        if (!is_string($token) || !$this->orderToken->isValid($orderId, $storeId, $expires, $token)) {
            $this->hasUntrustedOrderId = true;
            return 0;
        }

        return $orderId;
    }

    /**
     * Return the last order ID in this database
     *
     * @return int
     */
    private function getLastInsertedOrderId()
    {
        $orders = $this->getOrderSearchResult();
        if (false === $orders->getTotalCount() > 0) {
            return 0;
        }

        $orderItems = $orders->getItems();
        if (count($orderItems) < 1) {
            return 0;
        }

        $firstOrder = array_shift($orderItems);
        if (false === $firstOrder instanceof OrderInterface) {
            return 0;
        }

        return (int)$firstOrder->getEntityId();
    }

    /**
     * @return OrderInterface
     */
    private function getEmptyOrder()
    {
        return $this->orderFactory->create();
    }

    /**
     * @param $orderId
     *
     * @return OrderInterface
     */
    private function getOrderById($orderId)
    {
        if (empty($orderId)) {
            return $this->getEmptyOrder();
        }

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            return $this->getEmptyOrder();
        }

        if ((int)$order->getStoreId() !== $this->getCurrentStoreId()) {
            return $this->getEmptyOrder();
        }

        return $order;
    }

    /**
     * @return OrderSearchResultInterface
     */
    private function getOrderSearchResult(): OrderSearchResultInterface
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $searchCriteriaBuilder->addFilter('store_id', $this->getCurrentStoreId());
        $searchCriteriaBuilder->addSortOrder(
            $this->sortOrderBuilder->setField('created_at')->setDirection(SortOrder::SORT_DESC)->create()
        );

        $searchCriteria = $searchCriteriaBuilder->create();
        $searchCriteria->setPageSize(1);
        $searchCriteria->setCurrentPage(1);

        return $this->orderRepository->getList($searchCriteria);
    }

    private function getCurrentStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
