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

use Magento\Framework\App\Cache\StateInterface;

/**
 * Class CacheStateMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext
 */
trait CacheStateMock
{
    /**
     * @return StateInterface
     */
    protected function getCacheStateMock()
    {
        $mock = $this->createMock(StateInterface::class);

        $mock->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true);

        return $mock;
    }
}
