<?php declare(strict_types=1);
/**
 * CheckoutTester2 plugin for Magento
 *
 * @package     Yireo_CheckoutTester2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2022 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

namespace Yireo\CheckoutTester2\Test\Integration;

use PHPUnit\Framework\TestCase;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;

class ModuleConfigTest extends TestCase
{
    use AssertModuleIsEnabled;
    use AssertModuleIsRegistered;

    public function testModuleIsRegistered()
    {
        $this->assertModuleIsRegistered('Yireo_CheckoutTester2');
    }

    public function testModuleIsEnabled()
    {
        $this->assertModuleIsEnabled('Yireo_CheckoutTester2');
    }
}
