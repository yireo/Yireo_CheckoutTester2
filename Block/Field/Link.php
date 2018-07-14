<?php
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\CheckoutTester2\Block\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;

/**
 * Class Link
 *
 * @package Yireo\CheckoutTester2\Block\Field
 */
class Link extends Field
{
    /**
     * Note: This model is needed and not $_urlBuilder() or $_storeManager->getUrl() because of issue 5322
     *
     * @var Url
     */
    protected $urlModel;

    /**
     * Link constructor.
     *
     * @param Url $urlModel
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Url $urlModel,
        Context $context,
        array $data = []
    ) {
        $this->urlModel = $urlModel;
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
        $html = '<a href="' . $link . '" target="_new">'
            . __('Open success page in new window')
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
        $storeId = $this->getStoreId();
        $url = $this->urlModel->setScope($storeId)->getUrl('checkouttester/index/success');

        return $url;
    }

    /**
     * Return store id of current configuration scope
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getStoreId(): string
    {
        $storeId = $this->_request->getParam('store');
        if (!empty($storeId)) {
            return (string)$storeId;
        }

        return (string)$this->_storeManager->getStore()->getCode();
    }
}
