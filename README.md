# CheckoutTester module for Magento 2
Homepage: https://www.yireo.com/software/magento-extensions/checkout-tester-2

Requirements:
* Magento 2.0.0 Stable
* Magento 2.1.0 Stable
* PHP 7.0 or higher

## Installation
We recommend `composer` to install this package. Install our module using the following command:

    composer require yireo/magento2-checkouttester2

Next, install the new module into Magento itself:

* Run `php -f bin/magento module:enable Yireo_CheckoutTester2`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Configure settings under **Stores > Configuration > Advanced > Yireo CheckoutTester**
* Done

## Testing
This extension does not ship with unit tests or integration tests ... yet.
