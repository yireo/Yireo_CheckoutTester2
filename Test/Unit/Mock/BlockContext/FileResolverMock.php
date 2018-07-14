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

use Magento\Framework\View\Element\Template\File\Resolver;

/**
 * Class FileResolverMock
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\BlockContext
 */
trait FileResolverMock
{
    /**
     * @return Resolver
     */
    protected function getResolverMock()
    {
        $mock = $this->createMock(Resolver::class);

        $mock->expects($this->any())
            ->method('getTemplateFileName')
            ->willReturn('dummy.phtml');

        return $mock;
    }
}
