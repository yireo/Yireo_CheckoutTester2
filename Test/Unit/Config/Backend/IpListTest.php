<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Unit\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Config\Backend\IpList as Target;

class IpListTest extends TestCase
{
    public function testBeforeSaveAcceptsEmptyValue()
    {
        $target = $this->getTarget('', true);
        $this->assertSame($target, $target->beforeSave());
    }

    public function testBeforeSaveAcceptsValidIpAddresses()
    {
        $target = $this->getTarget(' 127.0.0.1, ,2001:db8::1 ,10.0.0.1,', false);
        $this->assertSame($target, $target->beforeSave());
    }

    public function testBeforeSaveRejectsInvalidIpAddress()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"foobar" is not a valid IP address.');
        $this->getTarget('127.0.0.1,foobar', true)->beforeSave();
    }

    public function testBeforeSaveRejectsIpRange()
    {
        $this->expectException(LocalizedException::class);
        $this->getTarget('10.0.0.0/8', true)->beforeSave();
    }

    public function testBeforeSaveAcceptsWildcardWhenOnlyInDevModeIsEnabled()
    {
        $target = $this->getTarget('127.0.0.1, *', true);
        $this->assertSame($target, $target->beforeSave());
    }

    public function testBeforeSaveRejectsWildcardWhenOnlyInDevModeIsDisabled()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('The wildcard "*" is only allowed');
        $this->getTarget('*', false)->beforeSave();
    }

    public function testBeforeSaveFallsBackToStoredOnlyInDevModeValue()
    {
        $target = $this->getTarget('*', null, '1');
        $this->assertSame($target, $target->beforeSave());

        $this->expectException(LocalizedException::class);
        $this->getTarget('*', null, '0')->beforeSave();
    }

    private function getTarget(string $value, ?bool $onlyInDevMode, ?string $storedOnlyInDevMode = null): Target
    {
        $context = $this->createMock(Context::class);
        $context->method('getEventDispatcher')->willReturn($this->createMock(ManagerInterface::class));

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnMap([
            ['checkouttester2/settings/only_in_dev_mode', 'default', null, $storedOnlyInDevMode],
        ]);

        $data = ['value' => $value, 'scope' => 'default'];
        if ($onlyInDevMode !== null) {
            $data['fieldset_data'] = ['only_in_dev_mode' => $onlyInDevMode ? '1' : '0'];
        }

        return new Target(
            $context,
            $this->createMock(Registry::class),
            $scopeConfig,
            $this->createMock(TypeListInterface::class),
            null,
            null,
            $data
        );
    }
}
