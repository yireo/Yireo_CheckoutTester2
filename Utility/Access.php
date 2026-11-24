<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Utility;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Yireo\CheckoutTester2\Config\Config;

class Access
{
    public const ALLOW_ALL = '*';

    private Config $config;
    private RequestInterface $request;

    public function __construct(
        Config $config,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Method to determine whether the current user has access to this page
     *
     * @return bool
     */
    public function hasAccess(): bool
    {
        $allowedIps = $this->splitList($this->config->getIpAddress());
        if (empty($allowedIps)) {
            return $this->config->isDeveloperMode() && $this->isLocalIpAddress($this->getCurrentIpAddress());
        }

        if (in_array(self::ALLOW_ALL, $allowedIps, true) && $this->config->isDeveloperMode()) {
            return true;
        }

        $currentIp = $this->getCurrentIpAddress();
        if ($currentIp === '') {
            return false;
        }

        foreach ($allowedIps as $allowedIp) {
            if ($this->normalizeIpAddress($allowedIp) === $currentIp) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the IP address of the connection. X-Forwarded-For is only used when the connection comes from a trusted proxy
     *
     * @return string
     */
    public function getCurrentIpAddress(): string
    {
        /** @var Request $request */
        $request = $this->request;
        $remoteIp = $this->normalizeIpAddress((string)$request->getServer('REMOTE_ADDR'));
        if ($remoteIp === '') {
            return '';
        }

        $trustedProxies = array_map(
            [$this, 'normalizeIpAddress'],
            $this->splitList($this->config->getTrustedProxies())
        );
        if (!in_array($remoteIp, $trustedProxies, true)) {
            return $remoteIp;
        }

        $forwardedIps = array_reverse($this->splitList((string)$request->getServer('HTTP_X_FORWARDED_FOR')));
        foreach ($forwardedIps as $forwardedIp) {
            $forwardedIp = $this->normalizeIpAddress($forwardedIp);
            if ($forwardedIp === '') {
                return '';
            }

            if (!in_array($forwardedIp, $trustedProxies, true)) {
                return $forwardedIp;
            }
        }

        return '';
    }

    /**
     * Check whether an IP address is a loopback, private or reserved address
     *
     * @param string $ip
     * @return bool
     */
    private function isLocalIpAddress(string $ip): bool
    {
        if ($ip === '') {
            return false;
        }

        if (stripos($ip, '::ffff:') === 0 && filter_var(substr($ip, 7), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ip = substr($ip, 7);
        }

        if ($ip === '::1' || strpos($ip, '127.') === 0) {
            return true;
        }

        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) === false;
    }

    /**
     * @param string $list
     * @return string[]
     */
    private function splitList(string $list): array
    {
        $items = [];
        foreach (explode(',', $list) as $item) {
            $item = trim($item, " \f\n\r\t\v\x00");
            if ($item !== '') {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function normalizeIpAddress(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return '';
        }

        $packedIp = inet_pton($ip);
        if ($packedIp === false) {
            return '';
        }

        return (string)inet_ntop($packedIp);
    }
}
