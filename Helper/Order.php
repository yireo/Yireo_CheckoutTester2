<?php
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_EmailTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\CheckoutTester2\Helper;

use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Yireo\CheckoutTester2\Factory\OrderFactory;

/**
 * Class \Yireo\CheckoutTester2\Helper\Order
 */
class Order
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Data constructor.
     *
     * @param OrderFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Return the last order ID in this database
     *
     * @return int
     */
    public function getLastInsertedOrderId()
    {
        $orders = $this->getOrderCollection();

        if (empty($orders)) {
            return 0;
        }

        $orderItems = $orders->getItems();
        if (empty($orderItems)) {
            return 0;
        }

        $firstOrder = array_shift($orderItems);
        if (empty($firstOrder)) {
            return 0;
        }

        return (int)$firstOrder->getEntityId();
    }

    /**
     * @return OrderInterface
     */
    public function getEmptyOrder()
    {
        return $this->orderFactory->create();
    }

    /**
     * @param $orderId
     *
     * @return OrderInterface
     */
    public function getOrderById($orderId)
    {
        if (empty($orderId)) {
            return $this->getEmptyOrder();
        }

        try {
            return $this->orderRepository->get($orderId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return $this->getEmptyOrder();
        }
    }

    /**
     * @return OrderSearchResultInterface
     */
    protected function getOrderCollection()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;
        $searchCriteriaBuilder->addSortOrder('created_at', AbstractCollection::SORT_ORDER_DESC);

        $searchCriteria = $searchCriteriaBuilder->create();
        $searchCriteria->setPageSize(1);
        $searchCriteria->setCurrentPage(0);
        $searchCriteria->getSortOrders();

        $orders = $this->orderRepository->getList($searchCriteria);

        return $orders;
    }
}
