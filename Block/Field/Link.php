<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Block\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Store\Api\Data\StoreInterface;
use Yireo\CheckoutTester2\Config\Config;
use Yireo\CheckoutTester2\Utility\OrderToken;

class Link extends Field
{
    public const TOKEN_LIFETIME = 3600;

    /**
     * Note: This model is needed and not $_urlBuilder() or $_storeManager->getUrl() because of issue 5322
     *
     * @var Url
     */
    protected $urlModel;

    private Config $config;
    private OrderToken $orderToken;

    /**
     * Link constructor.
     *
     * @param Url $urlModel
     * @param Context $context
     * @param Config $config
     * @param OrderToken $orderToken
     * @param array $data
     */
    public function __construct(
        Url $urlModel,
        Context $context,
        Config $config,
        OrderToken $orderToken,
        array $data = []
    ) {
        $this->urlModel = $urlModel;
        $this->config = $config;
        $this->orderToken = $orderToken;
        parent::__construct($context, $data);
    }

    /**
     * Return the elements HTML value
     *
     * @param AbstractElement $element
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $link = $this->getFrontendLink();
        $html = '<a href="' . $this->escapeUrl($link) . '" target="_blank" rel="noopener noreferrer">'
            . $this->escapeHtml(__('Open success page in new window'))
            . '</a>';

        return $html;
    }

    /**
     * Return the frontend link
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFrontendLink(): string
    {
        $store = $this->getStore();
        $storeId = (int)$store->getId();
        $params = [];

        $orderId = $this->config->getOrderIdFromConfig($storeId);
        if ($orderId > 0) {
            $expires = time() + self::TOKEN_LIFETIME;
            $params['_query'] = [
                'order_id' => $orderId,
                'expires' => $expires,
                'token' => $this->orderToken->create($orderId, $storeId, $expires),
            ];
        }

        return $this->urlModel->setScope($store->getCode())->getUrl('checkouttester/index/success', $params);
    }

    /**
     * Return store of current configuration scope
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    protected function getStore(): StoreInterface
    {
        $storeId = $this->_request->getParam('store');
        if (is_string($storeId) && $storeId !== '') {
            try {
                return $this->_storeManager->getStore($storeId);
            } catch (NoSuchEntityException $exception) {
                return $this->getDefaultStore();
            }
        }

        $websiteId = $this->_request->getParam('website');
        if (is_string($websiteId) && $websiteId !== '') {
            try {
                $website = $this->_storeManager->getWebsite($websiteId);
                $group = $this->_storeManager->getGroup($website->getDefaultGroupId());
                return $this->_storeManager->getStore($group->getDefaultStoreId());
            } catch (LocalizedException $exception) {
                return $this->getDefaultStore();
            }
        }

        return $this->getDefaultStore();
    }

    /**
     * Return the default store view
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getDefaultStore(): StoreInterface
    {
        $store = $this->_storeManager->getDefaultStoreView();
        if ($store instanceof StoreInterface) {
            return $store;
        }

        return $this->_storeManager->getStore();
    }
}
