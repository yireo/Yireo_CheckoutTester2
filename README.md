# CheckoutTester module for Magento 2

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-checkouttester2)

</div>
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
* Enable the module with the setting **Enabled** (the module is disabled by default), or via the CLI:

```bash
bin/magento config:set checkouttester2/settings/enabled 1
```

* Done

## Usage
When you customize Magento 2 to your needs, conversion is everything. So part of the customization process will definitely include the checkout process, including the success page that is visited once the checkout
is completed. But when you refresh the success page, the session is cleaned up and you are redirected back to the cart.

This extension is ment to solve this specific issue. Instead of hacking the Magento 2 core, you can install our Magento2 extension in a simple and clean manner, and preview the success-page at any time. You simply
point your browser to the preview URL and voila, there's your success page:

    http://MAGENTO2/checkouttester/index/success

By default, the order configured with the setting **Sales Order ID** is shown, or if that is empty, the latest order of
the current store view.

You can also test a specific order via the URL, but only with a signed token that expires:

    http://MAGENTO2/checkouttester/index/success?order_id=1234&expires=1767225600&token=abcdef...

The `token` is an HMAC-SHA256 signature (using the Magento encryption key) of `order_id|store_id|expires`. The link
**Usage** in the configuration of this module generates such a URL (valid for one hour) for the configured **Sales
Order ID**. An `order_id` without a valid token is ignored.

### Access
The success page shows real order data, so access is restricted with the setting **Development IP** (which, like
**Trusted Proxies**, can only be configured in the default scope):

- A comma-separated list of IP addresses only allows those addresses;
- `*` allows every IP address, but only in developer mode. In other modes, `*` is ignored. Saving `*` in the
  configuration is only possible when **Only in Developer Mode** is enabled. Invalid IP addresses can not be saved;
- When empty, only loopback, private and reserved IP addresses (like `127.0.0.1`, `::1`, `10.0.0.0/8`, `172.16.0.0/12`
  and `192.168.0.0/16`) are allowed, in developer mode only. Access is denied in other modes.

The IP address is the address of the connection (`REMOTE_ADDR`). Headers like `Client-IP` are ignored. The
`X-Forwarded-For` header is only used when the connection comes from an IP listed in the setting **Trusted Proxies**
(like Varnish or a load balancer); then the last address that is not a trusted proxy is used. If there is no such address, access is denied. Only orders of the current store view are shown. Blocked requests show a 404 page.

### Events
With the setting **Generate event**, the event `checkout_onepage_controller_success_action` is dispatched with every
page view. This only happens for trusted order sources: the configured **Sales Order ID**, the latest order or an
`order_id` with a valid token. When the request contains any other `order_id`, no event is dispatched.

## Upgrading (breaking changes)
This version hardens the security of the module. Check the following before upgrading:

- The module is disabled by default. Enable it with the setting **Enabled**.
- The URL parameter `order_id` is ignored unless it comes with a valid `expires` and `token` parameter. Use the link
  **Usage** in the configuration to generate a signed URL.
- The wildcard `*` in **Development IP** only works in developer mode, and can only be saved when **Only in Developer
  Mode** is enabled. Invalid IP addresses can not be saved.
- With an empty **Development IP**, only loopback, private and reserved client IPs have access (in developer mode).
- The settings **Development IP** (`checkouttester2/settings/ip`) and **Trusted Proxies**
  (`checkouttester2/settings/trusted_proxies`) are now read from the default scope only. Values on website or store
  view level are ignored: migrate them to the default scope before upgrading.
- When the shop runs behind a reverse proxy with a private IP (like Varnish or a load balancer) that is not listed in
  **Trusted Proxies**, every visitor appears to have the IP of that proxy. With an empty **Development IP** in developer
  mode, this would grant access to all visitors. Always add your own reverse proxies to **Trusted Proxies**.

### Tip
If you like this extension, make sure to view our EmailTester2 extension. We do not want to build a shop without it.

## PWA compatibility
This frontend extension will be completely replaced by React or Vue functionality, and therefore, this extension
will not be made compatible with PWA.

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_CheckoutTester2/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
