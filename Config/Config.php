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
        if ($onlyInDevMode && $this->appState->getMode() !== State::MODE_DEVELOPER) {
            return false;
        }

        return (bool)$this->getConfigValue('enabled');
    }

    /**
     * Get IP address that is allowed access
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        return (string)$this->getConfigValue('ip');
    }

    /**
     * Return the order ID
     *
     * @return int
     */
    public function getOrderIdFromConfig(): int
    {
        return (int)$this->getConfigValue('order_id');
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
     * @param bool $prefix
     *
     * @return mixed
     */
    public function getConfigValue(string $key = '', $defaultValue = null, $prefix = true)
    {
        if ($prefix) {
            $key = 'checkouttester2/settings/' . $key;
        }

        $value = $this->scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE
        );

        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }
}
