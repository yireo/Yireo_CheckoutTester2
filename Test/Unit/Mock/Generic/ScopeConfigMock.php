<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Mock\Generic;

use Magento\Framework\App\Config\ScopeConfigInterface;

trait ScopeConfigMock
{
    /**
     * @var array
     */
    protected $scopeConfigValues = [];

    /**
     * @return ScopeConfigInterface
     */
    protected function getScopeConfigMock(): ScopeConfigInterface
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
    protected function getScopeConfigValues(): array
    {
        return array_values($this->scopeConfigValues);
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    protected function setScopeConfigValue(string $name, $value): void
    {
        $scope = 'store';
        $this->scopeConfigValues[$name] = [$name, $scope, null, $value];
    }
}
