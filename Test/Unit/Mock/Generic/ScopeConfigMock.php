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

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class DataTest
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Mock\Generic
 */
trait ScopeConfigMock
{
    /**
     * @var array
     */
    protected $scopeConfigValues = [];

    /**
     * @return ScopeConfigInterface
     */
    protected function getScopeConfigMock()
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap($this->getScopeConfigValues()));

        return $scopeConfig;
    }

    /**
     * @return array
     */
    protected function getScopeConfigValues()
    {
        return array_values($this->scopeConfigValues);
    }

    /**
     * @return array
     */
    protected function setScopeConfigValue($name, $value)
    {
        $scope = 'store';
        $this->scopeConfigValues[$name] = [$name, $scope, null, $value];
    }
}
