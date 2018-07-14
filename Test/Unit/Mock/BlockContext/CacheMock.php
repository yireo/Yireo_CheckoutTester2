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

use Magento\Framework\App\CacheInterface;

/**
 * Class CacheMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext
 */
trait CacheMock
{
    /**
     * @return CacheInterface
     */
    protected function getCacheMock()
    {
        $mock = $this->createMock(CacheInterface::class);

        $mock->expects($this->any())
            ->method('load')
            ->willReturn('some data');

        return $mock;
    }
}
