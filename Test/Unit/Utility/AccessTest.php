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

    public function testHasAccessWithoutConfiguredIpAddressInDeveloperMode()
    {
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'])->hasAccess());
        $this->assertTrue($this->getTargetObject([])->hasAccess());
    }

    public function testHasNoAccessWithoutConfiguredIpAddressOutsideDeveloperMode()
    {
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'], State::MODE_PRODUCTION)->hasAccess());
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'], State::MODE_DEFAULT)->hasAccess());
    }

    public function testHasNoAccessWithWhitespaceAsConfiguredIpAddressOutsideDeveloperMode()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', ' , ');
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'], State::MODE_PRODUCTION)->hasAccess());
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'])->hasAccess());
    }

    public function testHasAccessWithWildcardInDeveloperMode()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '10.0.0.1, *');
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'])->hasAccess());
    }

    public function testHasNoAccessWithWildcardOutsideDeveloperMode()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '10.0.0.1, *');
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'], State::MODE_PRODUCTION)->hasAccess());
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'], State::MODE_DEFAULT)->hasAccess());
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '10.0.0.1'], State::MODE_PRODUCTION)->hasAccess());
    }

    public function testHasAccessWithMatchingIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'], State::MODE_PRODUCTION)->hasAccess());
    }

    public function testHasAccessWithMatchingIpAddressInList()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '10.0.0.1, ,1.2.3.4 ,');
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'])->hasAccess());
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '10.0.0.1'])->hasAccess());
    }

    public function testHasAccessWithDifferentlyNotatedIpv6Address()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '2001:DB8:0:0:0:0:0:1');
        $this->assertTrue($this->getTargetObject(['REMOTE_ADDR' => '2001:db8::1'])->hasAccess());
    }

    public function testHasNoAccessWithOtherIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '10.0.0.1,1.2.3.4');
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.5'])->hasAccess());
    }

    public function testHasNoAccessWithEmptyClientIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->assertFalse($this->getTargetObject([])->hasAccess());
    }

    public function testHasNoAccessWithInvalidConfiguredIpAddress()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', 'foobar');
        $this->assertFalse($this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'])->hasAccess());
    }

    public function testHasNoAccessWithSpoofedClientIpHeader()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $target = $this->getTargetObject(['REMOTE_ADDR' => '6.6.6.6', 'HTTP_CLIENT_IP' => '1.2.3.4']);
        $this->assertFalse($target->hasAccess());
        $this->assertSame('6.6.6.6', $target->getCurrentIpAddress());
    }

    public function testHasNoAccessWithSpoofedForwardedForHeader()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $target = $this->getTargetObject(['REMOTE_ADDR' => '6.6.6.6', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4, 6.6.6.6']);
        $this->assertFalse($target->hasAccess());
    }

    public function testHasAccessWithForwardedForHeaderFromTrustedProxy()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->setScopeConfigValue('checkouttester2/settings/trusted_proxies', '10.0.0.1, 10.0.0.2');
        $target = $this->getTargetObject(
            ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4, 10.0.0.2'],
            State::MODE_PRODUCTION
        );
        $this->assertTrue($target->hasAccess());
        $this->assertSame('1.2.3.4', $target->getCurrentIpAddress());
    }

    public function testHasNoAccessWithSpoofedForwardedForHeaderBehindTrustedProxy()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->setScopeConfigValue('checkouttester2/settings/trusted_proxies', '10.0.0.1');
        $target = $this->getTargetObject(
            ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4, 6.6.6.6'],
            State::MODE_PRODUCTION
        );
        $this->assertFalse($target->hasAccess());
        $this->assertSame('6.6.6.6', $target->getCurrentIpAddress());
    }

    public function testHasNoAccessWithInvalidForwardedForHeaderBehindTrustedProxy()
    {
        $this->setScopeConfigValue('checkouttester2/settings/ip', '1.2.3.4');
        $this->setScopeConfigValue('checkouttester2/settings/trusted_proxies', '10.0.0.1');
        $target = $this->getTargetObject(
            ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '1.2.3.4, foobar'],
            State::MODE_PRODUCTION
        );
        $this->assertFalse($target->hasAccess());
    }

    public function testGetCurrentIpAddress()
    {
        $this->assertSame('1.2.3.4', $this->getTargetObject(['REMOTE_ADDR' => '1.2.3.4'])->getCurrentIpAddress());
        $this->assertSame('', $this->getTargetObject([])->getCurrentIpAddress());
    }

    private function getTargetObject(
        array $server,
        string $mode = State::MODE_DEVELOPER
    ): Target {
        $appState = $this->createMock(State::class);
        $appState->method('getMode')->willReturn($mode);
        $config = new Config($this->getScopeConfigMock(), $appState);

        $request = $this->createMock(Http::class);
        $request->method('getServer')->willReturnCallback(
            fn ($name = null, $default = null) => $server[$name] ?? $default
        );

        return new Target($config, $request);
    }
}
