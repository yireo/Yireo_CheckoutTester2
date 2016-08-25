# CheckoutTester module for Magento 2
=========================================
Homepage: http://www.yireo.com/software/magento-extensions/checkout-tester-2

Requirements:
* Magento 2.0.0 Stable
* Magento 2.1.0 Stable

We recommend `composer` to install this package. However, if you want a manual copy instead, these are the steps:
* Upload the files in the `source/` folder to the folder `app/code/Yireo/CheckoutTester2` of your site
* Run `php -f bin/magento module:enable Yireo_CheckoutTester2`
* Run `php -f bin/magento setup:upgrade`
* Flush the Magento cache
* Configure settings under `Stores > Configuration > Advanced > Yireo CheckoutTester`
* Done

## Unit testing
This extension does not ship with unit tests.
