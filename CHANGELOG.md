# Changes in Diff Sniffer

## [3.5.0] - 2019-10-21

### Added

* Compatibility with PHP 7.4.

### Changed

* Updated [PHP\_CodeSniffer](https://packagist.org/packages/squizlabs/php_codesniffer) to ^3.5.
* Moved the core library directly to the project's repository.

## [3.4.1] - 2019-05-01

### Fixed

* Improved handling of coding standards with additional dependencies (#8, #9).

## [3.4.0] - 2019-03-01

### Changed

* Updated [PHP\_CodeSniffer](https://packagist.org/packages/squizlabs/php_codesniffer) to ^3.4.
* Bumped minimum required PHP version to 7.2.

### Fixed

* Improved handling of the `exclude-pattern` configuration parameter (#4, #5).

## [3.1.1] - 2017-12-04

### Fixed

* Excluded rules are still reported if caching is enabled (#3).

## [3.1.0] - 2017-12-02

### Added

* Support of phpcs.xml files (#1).
* Better integration with [dealerdirect/phpcodesniffer-composer-installer](https://github.com/DealerDirect/phpcodesniffer-composer-installer) (#2).

## [3.0.0] - 2017-10-29

### Changed

* Updated [PHP\_CodeSniffer](https://packagist.org/packages/squizlabs/php_codesniffer) to ^3.0.
* Bumped minimum required PHP version to 7.1.
* Internal improvements.
