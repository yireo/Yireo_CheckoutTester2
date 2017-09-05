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

namespace Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext;

/**
 * Class CacheMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext
 */
trait CacheMock
{
    /**
     * @return \Magento\Framework\App\CacheInterface
     */
    protected function getCacheMock()
    {
        $mock = $this->createMock('Magento\Framework\App\CacheInterface');

        $mock->expects($this->any())
            ->method('load')
            ->willReturn('some data');

        return $mock;
    }
}