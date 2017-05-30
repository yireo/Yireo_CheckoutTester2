# CheckoutTester module for Magento 2
=========================================
Homepage: http://www.yireo.com/software/magento-extensions/checkout-tester-2

Requirements:
* Magento 2.0.0 Stable
* Magento 2.1.0 Stable

## Installation
We recommend `composer` to install this package. Add our own free composer repository to your Magento 2 installation:

    composer config repositories.yireo-free composer https://satis.yireo.com

Next, install our module using the following command:

    composer require yireo/yireo_checkouttester2

Next, install the new module into Magento itself:

* Run `php -f bin/magento module:enable Yireo_CheckoutTester2`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Configure settings under **Stores > Configuration > Advanced > Yireo CheckoutTester**
* Done

## Unit testing
This extension does not ship with unit tests.
