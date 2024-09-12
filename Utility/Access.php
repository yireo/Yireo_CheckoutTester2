<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Utility;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Yireo\CheckoutTester2\Config\Config;

class Access
{
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
        $ip = trim($this->config->getIpAddress());
        if (!$ip) {
            return true;
        }

        $realIp = $this->getCurrentIpAddress();
        if (!$realIp) {
            return false;
        }

        $ips = explode(',', $ip);

        foreach ($ips as $ip) {
            $ip = trim($ip);

            if (empty($ip)) {
                continue;
            }

            if ($ip === $realIp) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the current IP address
     *
     * @return string
     */
    public function getCurrentIpAddress(): string
    {
        /** @var Request $request */
        $request = $this->request;
        $ip = (string)$request->getClientIp();
        $forwarded = explode(', ', $ip);
        return ($forwarded ? $forwarded[0] : $ip);
    }
}
