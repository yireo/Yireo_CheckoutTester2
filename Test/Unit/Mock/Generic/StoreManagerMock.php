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
 * Class StoreManagerMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\Generic
 */
trait StoreManagerMock
{
    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    protected function getStoreManagerMock()
    {
        $mock = $this->createMock('Magento\Store\Model\StoreManagerInterface');

        $mock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->getStoreMock());

        return $mock;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    protected function getStoreMock()
    {
        $mock = $this->createMock('Magento\Store\Api\Data\StoreInterface');

        $mock->expects($this->any())
            ->method('getCode')
            ->willReturn(42);

        return $mock;
    }
}