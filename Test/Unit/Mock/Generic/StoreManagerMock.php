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

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreManagerMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\Generic
 */
trait StoreManagerMock
{
    /**
     * @return StoreManagerInterface
     */
    protected function getStoreManagerMock()
    {
        $mock = $this->createMock(StoreManagerInterface::class);

        $mock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->getStoreMock());

        return $mock;
    }

    /**
     * @return StoreInterface
     */
    protected function getStoreMock()
    {
        $mock = $this->createMock(StoreInterface::class);

        $mock->expects($this->any())
            ->method('getCode')
            ->willReturn(42);

        return $mock;
    }
}
