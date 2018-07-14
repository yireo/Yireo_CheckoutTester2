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

namespace Yireo\CheckoutTester2\Test\Unit\Block\Field;

use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Block\Field\Link as Target;
use Yireo\CheckoutTester2\Test\Unit\Mock\BlockContextMock;

/**
 * Class LinkTest
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Block\Field
 */
class LinkTest extends TestCase
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
        $context = $this->getContextMock('adminhtml');
        $target = new Target($this->getUrlMock(), $context);
        return $target;
    }
}
