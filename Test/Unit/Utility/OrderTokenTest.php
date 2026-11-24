<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Test\Unit\Utility;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CheckoutTester2\Utility\OrderToken as Target;

class OrderTokenTest extends TestCase
{
    public function testCreateSignsOrderStoreAndExpiration()
    {
        $encryptor = $this->createMock(EncryptorInterface::class);
        $encryptor->expects($this->once())
            ->method('hash')
            ->with('42|1|1234567890', Encryptor::HASH_VERSION_SHA256)
            ->willReturn('signature');

        $this->assertSame('signature', (new Target($encryptor))->create(42, 1, 1234567890));
    }

    public function testIsValidWithValidToken()
    {
        $target = $this->getTarget();
        $expires = time() + 3600;
        $this->assertTrue($target->isValid(42, 1, $expires, $target->create(42, 1, $expires)));
    }

    public function testIsNotValidWithExpiredToken()
    {
        $target = $this->getTarget();
        $expires = time() - 1;
        $this->assertFalse($target->isValid(42, 1, $expires, $target->create(42, 1, $expires)));
        $this->assertFalse($target->isValid(42, 1, time(), $target->create(42, 1, time())));
    }

    public function testIsNotValidWithTamperedData()
    {
        $target = $this->getTarget();
        $expires = time() + 3600;
        $token = $target->create(42, 1, $expires);

        $this->assertFalse($target->isValid(43, 1, $expires, $token));
        $this->assertFalse($target->isValid(42, 2, $expires, $token));
        $this->assertFalse($target->isValid(42, 1, $expires + 1, $token));
        $this->assertFalse($target->isValid(42, 1, $expires, $token . 'a'));
        $this->assertFalse($target->isValid(42, 1, $expires, ''));
    }

    public function testIsNotValidWithoutOrderId()
    {
        $target = $this->getTarget();
        $expires = time() + 3600;
        $this->assertFalse($target->isValid(0, 1, $expires, $target->create(0, 1, $expires)));
    }

    private function getTarget(): Target
    {
        $encryptor = $this->createMock(EncryptorInterface::class);
        $encryptor->method('hash')->willReturnCallback(
            fn ($data) => hash_hmac('sha256', (string)$data, 'secret')
        );

        return new Target($encryptor);
    }
}
