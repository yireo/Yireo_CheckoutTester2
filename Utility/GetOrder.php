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
    private RequestInterface $request;
    private Config $config;
    private OrderFactory $orderFactory;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SortOrderBuilder $sortOrderBuilder;
    private StoreManagerInterface $storeManager;

    public function __construct(
        RequestInterface $request,
        Config $config,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Method to fetch the current order
     *
     * @return OrderInterface
     */
    public function get(): OrderInterface
    {
        $orderIdFromUrl = (int)$this->request->getParam('order_id');
        $order = $this->getOrderById($orderIdFromUrl);
        if ($order->getEntityId()) {
            return $order;
        }

        $orderIdFromConfig = $this->config->getOrderIdFromConfig();
        $order = $this->getOrderById($orderIdFromConfig);
        if ($order->getEntityId()) {
            return $order;
        }

        $lastOrderId = $this->getLastInsertedOrderId();
        $order = $this->getOrderById($lastOrderId);

        if ($order->getEntityId()) {
            return $order;
        }

        return $this->getEmptyOrder();
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
