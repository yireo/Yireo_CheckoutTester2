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
 * Class AppStateMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext
 */
trait AppStateMock
{
    /**
     * @return \Magento\Framework\App\State
     */
    protected function getAppStateMock()
    {
        $mock = $this->createMock('Magento\Framework\App\State');

        $mock->expects($this->any())
            ->method('getAreaCode')
            ->willReturn('frontend');

        return $mock;
    }
}