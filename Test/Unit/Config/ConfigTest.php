<?php declare(strict_types=1);
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Unit\Config;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use \Yireo\CheckoutTester2\Config\Config as Target;
use Yireo\CheckoutTester2\Test\Unit\Mock\HelperContextMock;

class ConfigTest extends TestCase
{
    /**
     * Import mocking behaviour
     */
    use HelperContextMock;

    /**
     * Test whether the enabled flag works
     */
    public function testEnabled()
    {
        $this->setScopeConfigValue('checkouttester2/settings/enabled', 1);
        $target = $this->getTargetObject();
        $this->assertSame(true, $target->enabled());

        $this->setScopeConfigValue('checkouttester2/settings/enabled', 0);
        $target = $this->getTargetObject();
        $this->assertSame(false, $target->enabled());
    }

    /**
     * Test whether the URL returns some value
     */
    public function testGetOrderIdFromConfig()
    {
        $this->setScopeConfigValue('checkouttester2/settings/order_id', '42');
        $target = $this->getTargetObject();
        $this->assertSame(42, $target->getOrderIdFromConfig());
        $this->assertNotSame(41, $target->getOrderIdFromConfig());
    }

    /**
     * Test whether the URL returns some value
     */
    public function testAllowDispatchCheckoutOnepageControllerSuccessAction()
    {
        $this->setScopeConfigValue('checkouttester2/settings/checkout_onepage_controller_success_action', 1);
        $target = $this->getTargetObject();
        $this->assertSame(true, $target->allowDispatchCheckoutOnepageControllerSuccessAction());

        $this->setScopeConfigValue('checkouttester2/settings/checkout_onepage_controller_success_action', 0);
        $target = $this->getTargetObject();
        $this->assertSame(false, $target->allowDispatchCheckoutOnepageControllerSuccessAction());
    }

    public function testEnabledOnlyInDeveloperMode()
    {
        $this->setScopeConfigValue('checkouttester2/settings/enabled', 1);
        $this->setScopeConfigValue('checkouttester2/settings/only_in_dev_mode', 1);

        $target = $this->getTargetObject(State::MODE_DEVELOPER);
        $this->assertSame(true, $target->enabled());

        $target = $this->getTargetObject(State::MODE_PRODUCTION);
        $this->assertSame(false, $target->enabled());

        $target = $this->getTargetObject(State::MODE_DEFAULT);
        $this->assertSame(false, $target->enabled());
    }

    public function testEnabledInProductionModeWhenNotRestrictedToDeveloperMode()
    {
        $this->setScopeConfigValue('checkouttester2/settings/enabled', 1);
        $this->setScopeConfigValue('checkouttester2/settings/only_in_dev_mode', 0);

        $target = $this->getTargetObject(State::MODE_PRODUCTION);
        $this->assertSame(true, $target->enabled());
    }

    public function testDisabledInDeveloperMode()
    {
        $this->setScopeConfigValue('checkouttester2/settings/enabled', 0);
        $this->setScopeConfigValue('checkouttester2/settings/only_in_dev_mode', 1);

        $target = $this->getTargetObject(State::MODE_DEVELOPER);
        $this->assertSame(false, $target->enabled());
    }

    public function testGetIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '127.0.0.1,10.0.0.1');
        $target = $this->getTargetObject();
        $this->assertSame('127.0.0.1,10.0.0.1', $target->getIpAddress());
    }

    public function testGetIpAddressWhenEmpty()
    {
        $target = $this->getTargetObject();
        $this->assertSame('', $target->getIpAddress());
    }

    public function testGetConfigValueReturnsDefaultValueForEmptyValue()
    {
        $this->setScopeConfigValue('checkouttester2/settings/foobar', '');
        $target = $this->getTargetObject();
        $this->assertSame('default', $target->getConfigValue('foobar', 'default'));
        $this->assertNull($target->getConfigValue('foobar'));
    }

    public function testGetConfigValueWithoutPrefix()
    {
        $this->setScopeConfigValue('general/locale/code', 'nl_NL');
        $target = $this->getTargetObject();
        $this->assertSame('nl_NL', $target->getConfigValue('general/locale/code', null, false));
        $this->assertNull($target->getConfigValue('general/locale/code'));
    }

    /**
     * @return Target
     */
    protected function getTargetObject(string $mode = State::MODE_DEFAULT)
    {
        $scopeConfig = $this->getScopeConfigMock();
        $appState = $this->createMock(State::class);
        $appState->method('getMode')->willReturn($mode);
        $target = new Target($scopeConfig, $appState);

        return $target;
    }

    /**
     * @return MockObject
     */
    protected function getRequestMock()
    {
        $request = $this->createMock(Http::class);

        $request->expects($this->any())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');

        return $request;
    }
}
