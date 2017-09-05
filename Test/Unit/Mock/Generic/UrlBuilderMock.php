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

namespace Yireo\CheckoutTester2\Test\Unit\Mock\Generic;

/**
 * Class UrlBuilderMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\Generic
 */
trait UrlBuilderMock
{
    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function getUrlBuilderMock()
    {
        $mock = $this->createMock('Magento\Framework\UrlInterface');

        $mock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://www.example.com/');

        return $mock;
    }

    /**
     * @return \Magento\Framework\Url
     */
    protected function getUrlMock()
    {
        $mock = $this->createMock('Magento\Framework\Url');

        $mock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://www.example.com/');

        return $mock;
    }
}