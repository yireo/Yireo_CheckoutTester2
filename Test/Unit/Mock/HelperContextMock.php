<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Mock;

use Magento\Framework\App\Helper\Context;
use Yireo\CheckoutTester2\Test\Unit\Mock\Generic\ScopeConfigMock;

trait HelperContextMock
{
    /**
     * Import mocking behaviour
     */
    use ScopeConfigMock;

    /**
     * @return Context
     */
    protected function getContextMock()
    {
        $context = $this->createMock(Context::class);
        $scopeConfig = $this->getScopeConfigMock();
        $context->expects($this->any())
            ->method('getScopeConfig')
            ->will($this->returnValue($scopeConfig));

        return $context;
    }
}
