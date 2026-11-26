# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 25 November 2026
### Changed
- BREAKING: The module is now disabled by default, so installing it no longer exposes order data; enable it with the setting Enabled (checkouttester2/settings/enabled)
- BREAKING: The order_id URL parameter is ignored unless it carries a valid, non-expired signed token; use the link in the module configuration to generate such a URL
- BREAKING: The wildcard "*" in Development IP only grants access in developer mode, and invalid IP entries can no longer be saved
- BREAKING: With an empty Development IP setting, only loopback and private network addresses get access (in developer mode), and a missing client IP never grants access
- The settings Development IP and Trusted Proxies are only configurable and read in the default scope, so a website or store view can no longer widen access

### Fixed
- Fix the checkout success event being dispatched for untrusted or malformed order IDs, which could trigger tracking and other observers for arbitrary orders
- Fix a trusted proxy being treated as the client IP when X-Forwarded-For has no other address
- Fix the success page offering account creation for the shown order and possibly being stored in the full page cache
- Fix the admin link pointing to the wrong store view when store codes are not in URLs, and fall back to the default store when the selected store or website does not exist
- Fix sponsor links in the admin exposing the admin window and URL to the external page

## [1.0.6] - 26 November 2026
### Security
- Backport of the security fixes of 2.0.0 for YIREO-CHECKOUTTESTER2-2026-001 (order data disclosure via unsigned order_id parameter). This patch release deliberately contains backwards incompatible changes, because security takes precedence over semantic versioning

### Changed
- BREAKING: The module is now disabled by default, so installing it no longer exposes order data; enable it with the setting Enabled (checkouttester2/settings/enabled)
- BREAKING: The order_id URL parameter is ignored unless it carries a valid, non-expired signed token; use the link in the module configuration to generate such a URL
- BREAKING: The wildcard "*" in Development IP only grants access in developer mode, and invalid IP entries can no longer be saved
- BREAKING: With an empty Development IP setting, only loopback and private network addresses get access (in developer mode), and a missing client IP never grants access
- The settings Development IP and Trusted Proxies are only configurable and read in the default scope, so a website or store view can no longer widen access

### Fixed
- Fix the checkout success event being dispatched for untrusted or malformed order IDs, which could trigger tracking and other observers for arbitrary orders
- Fix a trusted proxy being treated as the client IP when X-Forwarded-For has no other address
- Fix the success page offering account creation for the shown order and possibly being stored in the full page cache
- Fix the admin link pointing to the wrong store view when store codes are not in URLs, and fall back to the default store when the selected store or website does not exist
- Fix sponsor links in the admin exposing the admin window and URL to the external page

## [1.0.5] - 23 November 2026
### Fixed
- Prevent IP allowlist bypass and guest order takeover
- Merge Markdown files into README
- Set use_store at store scope for Magento 2.4.8 compatibility
- Replace order_id request param with the resolved order ID
- Add success page tests for Luma and Hyva
- Cover order selection, session registration, page meta and admin config section
- Cover Access, Success controller guards and remaining Config methods
- Constrain phpunit and drop unused composer/composer from require-dev
- Pass explicit characters to trim() for PHP 8.6 compatibility
- Replace real-environment module check with integration-test-helper traits
- Beautify README
- Update MODULE.json
- Remove background of sponsoring box for Nebula compat

### Removed
- Remove PHP 8.1 support, add PHP 8.5 support

## [1.0.4] - 24 October 2024
### Fixed
- Add funding options

## [1.0.3] - 12 September 2023
### Fixed
- Unit test fixes

## [1.0.2] - 12 September 2023
### Fixed
- PHPStan fixes

## [1.0.1] - 19 July 2023
### Fixed
- Setting to only run this in Developer Mode

## [1.0.0] - 22 September 2021
### Added
- Separate `Config` class
- Added PHP 7.4+ syntax
- Rewrite controller to use `HttpGetActionInterface` only
- More helpful comments with configuration options

### Removed
- Remove legacy data-helper
- Removed support for PHP 7.4
- Removed support for Magento 2.2 or older
- Removed unneeded Success-block
- Remove `setup_version` from `module.xml`

## [0.0.16] - 4 August 2022
### Fixed
- Missing order data in event (@JamesFX2) #23

## [0.0.15] - 27 August 2021
### Added
- NOINDEX,NOFOLLOW
- Magento PHPCS compliance

## [0.0.14] - 20 October 2020
### Fixed
- Remove page layout specification in XML layout (which is duplicate) (@GrimLink)

## [0.0.13] - 29 July 2020
### Added
- Magento 2.4 support

## [0.0.12] - August 2019
### Added
- Proper integration testing for browsing depending on config settings

### Fixed
- Settings for "enabled" and "ip" were not properly picked up

## [0.0.11] - July 2019
### Added
- Add `version` to `composer.json`
- Add `etc/config.xml` file with default values
- Add KeepAChangeLog support
- Move configuration to separate **Yireo** section

For older releases, see the GitHub commit messages.
