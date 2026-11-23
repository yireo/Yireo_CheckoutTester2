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
            return $this->config->isDeveloperMode();
        }

        if (in_array(self::ALLOW_ALL, $allowedIps, true)) {
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

        $trustedProxies = array_map([$this, 'normalizeIpAddress'], $this->splitList($this->config->getTrustedProxies()));
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

        return $remoteIp;
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
