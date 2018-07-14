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

namespace Yireo\CheckoutTester2\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Block\Success as Target;
use Yireo\CheckoutTester2\Test\Unit\Mock\BlockContextMock;

/**
 * Class SuccessTest
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Block
 */
class SuccessTest extends TestCase
{
    /**
     * @var string
     */
    protected $moduleName = 'Yireo_CheckoutTester2';

    /**
     * Import mocking behaviour
     */
    use BlockContextMock;

    /**
     * @return Target
     */
    protected function getTarget()
    {
        $context = $this->getContextMock();
        $target = new Target($context);
        return $target;
    }
}
