<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Mock\Generic;

use Magento\Framework\Url;
use Magento\Framework\UrlInterface;

trait UrlBuilderMock
{
    /**
     * @return UrlInterface
     */
    protected function getUrlBuilderMock()
    {
        $mock = $this->createMock(UrlInterface::class);

        $mock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://www.example.com/');

        return $mock;
    }

    /**
     * @return Url
     */
    protected function getUrlMock()
    {
        $mock = $this->createMock(Url::class);

        $mock->expects($this->any())
            ->method('getUrl')
            ->willReturn('http://www.example.com/');

        return $mock;
    }
}
