<?php
/**
 * Yireo CheckoutTester2 for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Unit\Helper;

use Magento\Framework\App\Request\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use \Yireo\CheckoutTester2\Helper\Data as Target;
use Yireo\CheckoutTester2\Test\Unit\Mock\HelperContextMock;

/**
 * Class DataTest
 *
 * @package Yireo\CheckoutTester2\Test\Unit\Helper
 */
class DataTest extends TestCase
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
    public function testHasAccess()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '127.0.0.1');
        $target = $this->getTargetObject();
        $this->assertSame(true, $target->hasAccess());
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

    /**
     * @return Target
     */
    protected function getTargetObject()
    {
        $context = $this->getContextMock();

        $request = $this->getRequestMock();
        $context->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $target = new Target($context);

        return $target;
    }

    /**
     * @return MockObject
     */
    protected function getRequestMock()
    {
        $request = $this->createMock(
            Http::class,
            [],
            [],
            '',
            false,
            false
        );

        $request->expects($this->any())
            ->method('getClientIp')
            ->willReturn('127.0.0.1');

        return $request;
    }
}
