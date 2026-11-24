<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private ScopeConfigInterface $scopeConfig;
    private State $appState;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        State $appState
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
    }

    /**
     * Switch to determine whether this extension is enabled or not
     *
     * @return bool
     */
    public function enabled(): bool
    {
        $onlyInDevMode = (bool)$this->getConfigValue('only_in_dev_mode');
        if ($onlyInDevMode && !$this->isDeveloperMode()) {
            return false;
        }

        return (bool)$this->getConfigValue('enabled');
    }

    /**
     * Check whether Magento runs in developer mode
     *
     * @return bool
     */
    public function isDeveloperMode(): bool
    {
        return $this->appState->getMode() === State::MODE_DEVELOPER;
    }

    /**
     * Get IP address that is allowed access
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        return (string)$this->getDefaultConfigValue('ip');
    }

    /**
     * Get the IP addresses of reverse proxies whose X-Forwarded-For header is trusted
     *
     * @return string
     */
    public function getTrustedProxies(): string
    {
        return (string)$this->getDefaultConfigValue('trusted_proxies');
    }

    /**
     * Return the order ID
     *
     * @param int|null $storeId
     * @return int
     */
    public function getOrderIdFromConfig(?int $storeId = null): int
    {
        return (int)$this->getConfigValue('order_id', null, $storeId);
    }

    /**
     * Check whether the module is enabled
     *
     * @return bool
     */
    public function allowDispatchCheckoutOnepageControllerSuccessAction(): bool
    {
        return (bool)$this->getConfigValue('checkout_onepage_controller_success_action', false);
    }

    /**
     * Return a configuration value
     *
     * @param string $key
     * @param mixed $defaultValue
     * @param int|null $storeId
     *
     * @return mixed
     */
    private function getConfigValue(string $key, $defaultValue = null, ?int $storeId = null)
    {
        $value = $this->scopeConfig->getValue(
            'checkouttester2/settings/' . $key,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * Return a configuration value of the default scope
     *
     * @param string $key
     *
     * @return mixed
     */
    private function getDefaultConfigValue(string $key)
    {
        return $this->scopeConfig->getValue(
            'checkouttester2/settings/' . $key,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
