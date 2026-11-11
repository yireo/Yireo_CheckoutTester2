# CheckoutTester module for Magento 2

<!-- badges.specs.start -->
![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-checkouttester2)
<!-- badges.specs.end -->


## Installation
We recommend `composer` to install this package. Install our module using the following command:

```bash
composer require yireo/magento2-checkouttester2
```

Next, install the new module into Magento itself. The following steps assume you are using the Developer Mode for installing modules. If you are using the Production Mode, consult your developer for help.

```bash
bin/magento module:enable Yireo_CheckoutTester2
bin/magento setup:upgrade
```

* Flush the Magento cache
* Configure settings under **Stores > Configuration > Yireo > Yireo CheckoutTester**
* Done

## Usage
When you customize Magento 2 to your needs, conversion is everything. So part of the customization process will definitely include the checkout process, including the success page that is visited once the checkout
is completed. But when you refresh the success page, the session is cleaned up and you are redirected back to the cart.

This extension is ment to solve this specific issue. Instead of hacking the Magento 2 core, you can install our Magento2 extension in a simple and clean manner, and preview the success-page at any time. You simply
point your browser to the preview URL and voila, there's your success page:

    http://MAGENTO2/checkouttester/index/success

You can also test a specific order with the following:

    http://MAGENTO2/checkouttester/index/success/order_id/1234

### Tip
If you like this extension, make sure to view our EmailTester2 extension. We do not want to build a shop without it.

## PWA compatibility
This frontend extension will be completely replaced by React or Vue functionality, and therefore, this extension
will not be made compatible with PWA.

## Current status

<!-- badges.test.start -->
![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/compile.yml?label=compile)
<!-- badges.test.end -->
