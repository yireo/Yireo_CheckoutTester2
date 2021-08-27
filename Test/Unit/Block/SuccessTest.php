<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Block\Success as Target;
use Yireo\CheckoutTester2\Test\Unit\Mock\BlockContextMock;

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
