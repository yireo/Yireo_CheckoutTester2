<?php
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types = 1);
namespace Yireo\CheckoutTester2\Block\Field;

/**
 * Class Link
 *
 * @package Yireo\CheckoutTester2\Block\Field
 */
class Link extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Note: This model is needed and not $_urlBuilder() or $_storeManager->getUrl() because of issue 5322
     *
     * @var \Magento\Framework\Url
     */
    protected $urlModel;

    /**
     * Link constructor.
     *
     * @param \Magento\Framework\Url $urlModel
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Url $urlModel,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [])
    {
        $this->urlModel = $urlModel;
        parent::__construct($context, $data);
    }

    /**
     * Return the elements HTML value
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element): string
    {
        $link = $this->getFrontendLink();
        $html = '<a href="' . $link . '" target="_new">'
            . __('Open success page in new window')
            . '</a>';

        return $html;
    }

    /*public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->getElementHtml();
    }*/

    /**
     * Return the frontend link
     *
     * @return string
     */
    public function getFrontendLink(): string
    {
        $storeId = $this->_getStoreId();
        $url = $this->urlModel->setScope($storeId)->getUrl('checkouttester/index/success');

        return $url;
    }

    /**
     * Return store id of current configuration scope
     *
     * @return string
     */
    protected function _getStoreId(): string
    {
        $storeId = $this->_request->getParam('store');
        if (!empty($storeId)) {
            return (string) $storeId;
        }

        return (string)$this->_storeManager->getStore()->getCode();
    }
}