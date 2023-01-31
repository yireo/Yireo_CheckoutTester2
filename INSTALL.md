# Installation
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
