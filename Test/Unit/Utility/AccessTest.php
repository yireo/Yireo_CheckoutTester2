<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Unit\Utility;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Config\Config;
use Yireo\CheckoutTester2\Test\Unit\Mock\Generic\ScopeConfigMock;
use Yireo\CheckoutTester2\Utility\Access as Target;

class AccessTest extends TestCase
{
    use ScopeConfigMock;

    public function testHasAccessWithoutConfiguredIpAddress()
    {
        $this->assertTrue($this->getTargetObject('1.2.3.4')->hasAccess());
        $this->assertTrue($this->getTargetObject('')->hasAccess());
    }

    public function testHasAccessWithWhitespaceAsConfiguredIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '  ');
        $this->assertTrue($this->getTargetObject('1.2.3.4')->hasAccess());
    }

    public function testHasAccessWithMatchingIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->assertTrue($this->getTargetObject('1.2.3.4')->hasAccess());
    }

    public function testHasAccessWithMatchingIpAddressInList()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '10.0.0.1, ,1.2.3.4 ,');
        $this->assertTrue($this->getTargetObject('1.2.3.4')->hasAccess());
        $this->assertTrue($this->getTargetObject('10.0.0.1')->hasAccess());
    }

    public function testHasNoAccessWithOtherIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '10.0.0.1,1.2.3.4');
        $this->assertFalse($this->getTargetObject('1.2.3.5')->hasAccess());
    }

    public function testHasNoAccessWithEmptyClientIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->assertFalse($this->getTargetObject('')->hasAccess());
    }

    public function testGetCurrentIpAddress()
    {
        $this->assertSame('1.2.3.4', $this->getTargetObject('1.2.3.4')->getCurrentIpAddress());
        $this->assertSame('1.2.3.4', $this->getTargetObject('1.2.3.4, 10.0.0.1')->getCurrentIpAddress());
    }

    private function getTargetObject(string $clientIp): Target
    {
        $config = new Config($this->getScopeConfigMock(), $this->createMock(State::class));

        $request = $this->createMock(Http::class);
        $request->method('getClientIp')->willReturn($clientIp);

        return new Target($config, $request);
    }
}
