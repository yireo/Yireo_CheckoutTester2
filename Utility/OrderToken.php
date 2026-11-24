<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Utility;

use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;

class OrderToken
{
    private EncryptorInterface $encryptor;

    public function __construct(
        EncryptorInterface $encryptor
    ) {
        $this->encryptor = $encryptor;
    }

    /**
     * Create a signed token for showing a specific order in a specific store until a specific time
     *
     * @param int $orderId
     * @param int $storeId
     * @param int $expires
     * @return string
     */
    public function create(int $orderId, int $storeId, int $expires): string
    {
        return $this->encryptor->hash(
            $orderId . '|' . $storeId . '|' . $expires,
            Encryptor::HASH_VERSION_SHA256
        );
    }

    /**
     * Check whether a token is valid for the given order, store and expiration time
     *
     * @param int $orderId
     * @param int $storeId
     * @param int $expires
     * @param string $token
     * @return bool
     */
    public function isValid(int $orderId, int $storeId, int $expires, string $token): bool
    {
        if ($orderId < 1 || $token === '' || $expires <= time()) {
            return false;
        }

        return hash_equals($this->create($orderId, $storeId, $expires), $token);
    }
}
