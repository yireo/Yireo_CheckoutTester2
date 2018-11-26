# CheckoutTester module for Magento 2
Homepage: https://www.yireo.com/software/magento-extensions/checkout-tester-2

## Installation
We recommend `composer` to install this package. Install our module using the following command:

    composer require yireo/magento2-checkouttester2

Next, install the new module into Magento itself. The following steps assume you are using the Developer Mode for installing modules. If you are using the Production Mode, consult your developer for help.

* Run `php -f bin/magento module:enable Yireo_CheckoutTester2`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Configure settings under **Stores > Configuration > Advanced > Yireo CheckoutTester**
* Done
