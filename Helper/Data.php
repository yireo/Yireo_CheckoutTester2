<?php
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_EmailTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Helper;

use Magento\Framework\Api\Filter;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class \Yireo\CheckoutTester2\Helper\Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        return parent::__construct($context);
    }

    /**
     * Switch to determine whether this extension is enabled or not
     *
     * @return bool
     */
    public function enabled()
    {
        if ((bool)$this->getConfigValue('advanced/modules_disable_output/Yireo_CheckoutTester2')) {
            return false;
        }

        return true;
    }

    /**
     * Method to determine whether the current user has access to this page
     *
     * @return bool
     */
    public function hasAccess()
    {
        $ip = $this->getConfigValue('ip');
        $ip = trim($ip);

        $realIp = $this->getIpAddress();

        if (!empty($ip) && $realIp) {
            $ips = explode(',', $ip);

            foreach ($ips as $ip) {
                $ip = trim($ip);

                if (empty($ip)) {
                    continue;
                }

                if ($ip == $realIp) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Get the current IP address
     *
     * @return mixed
     */
    public function getIpAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];

        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Return the order ID
     *
     * @return string
     */
    public function getOrderIdFromConfig()
    {
        return (int)$this->getConfigValue('order_id');
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
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
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

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function allowDispatchCheckoutOnepageControllerSuccessAction()
    {
        return (bool)$this->getConfigValue('checkout_onepage_controller_success_action', false);
    }

    /**
     * Return a configuration value
     *
     * @param null $key
     * @param null $defaultValue
     * @param null $prefix
     *
     * @return mixed|null
     */
    public function getConfigValue($key = null, $defaultValue = null, $prefix = true)
    {
        if ($prefix) {
            $key = 'checkouttester2/settings/' . $key;
        }

        $value = $this->scopeConfig->getValue(
            'checkouttester2/settings/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }
}