<?php
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2016 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Block\Field;

/**
 * Class Link
 *
 * @package Yireo\CheckoutTester2\Block\Field
 */
class Link extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Return the elements HTML value
     *
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
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
    public function getFrontendLink()
    {
        $storeId = $this->_getStoreId();
        return $this->_storeManager->getStore($storeId)->getUrl('checkouttester/index/success');
    }

    /**
     * Return store id of current configuration scope
     *
     * @return int
     */
    protected function _getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}