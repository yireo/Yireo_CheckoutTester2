<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Utility;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\CheckoutTester2\Config\Config;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;

class GetOrder
{
    private RequestInterface $request;
    private Config $config;
    private OrderFactory $orderFactory;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        RequestInterface $request,
        Config $config,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
            return $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $exception) {
            return $this->getEmptyOrder();
        }
    }

    /**
     * @return OrderSearchResultInterface
     */
    private function getOrderSearchResult(): OrderSearchResultInterface
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $searchCriteriaBuilder->addSortOrder('created_at', AbstractCollection::SORT_ORDER_DESC);

        $searchCriteria = $searchCriteriaBuilder->create();
        $searchCriteria->setPageSize(1);
        $searchCriteria->setCurrentPage(0);
        $searchCriteria->getSortOrders();

        return $this->orderRepository->getList($searchCriteria);
    }
}
