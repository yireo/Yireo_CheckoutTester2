# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
