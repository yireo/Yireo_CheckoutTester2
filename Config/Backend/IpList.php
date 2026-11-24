<?php declare(strict_types=1);

namespace Yireo\CheckoutTester2\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Yireo\CheckoutTester2\Utility\Access;

class IpList extends Value
{
    /**
     * Validate the list of IP addresses before saving it
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        foreach (explode(',', (string)$this->getValue()) as $entry) {
            $entry = trim($entry, " \f\n\r\t\v\x00");
            if ($entry === '') {
                continue;
            }

            if ($entry === Access::ALLOW_ALL) {
                if (!$this->isOnlyInDevMode()) {
                    throw new LocalizedException(
                        __('The wildcard "*" is only allowed when "Only in Developer Mode" is enabled.')
                    );
                }

                continue;
            }

            if (filter_var($entry, FILTER_VALIDATE_IP) === false) {
                throw new LocalizedException(__('"%1" is not a valid IP address.', $entry));
            }
        }

        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    private function isOnlyInDevMode(): bool
    {
        $value = $this->getFieldsetDataValue('only_in_dev_mode');
        if ($value !== null) {
            return (bool)$value;
        }

        $scope = (string)$this->getScope();
        return (bool)$this->_config->getValue(
            'checkouttester2/settings/only_in_dev_mode',
            $scope !== '' ? $scope : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeCode()
        );
    }
}
