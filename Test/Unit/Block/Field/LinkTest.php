<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Block\Field;

use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Block\Field\Link as Target;
use Yireo\CheckoutTester2\Test\Unit\Mock\BlockContextMock;

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
