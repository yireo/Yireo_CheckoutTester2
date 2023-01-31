<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext;

use Magento\Framework\Session\SidResolverInterface;

trait SidResolverMock
{
    /**
     * @return SidResolverInterface
     */
    protected function getSidResolverMock()
    {
        $mock = $this->createMock(SidResolverInterface::class);

        $mock->expects($this->any())
            ->method('getSessionIdQueryParam')
            ->willReturn(42);

        return $mock;
    }
}
