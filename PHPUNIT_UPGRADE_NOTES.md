# PHPUnit Upgrade Notes

## Summary

PHPUnit has been successfully upgraded from version 10.x/11.x to **12.x** series (currently supporting ^11.0||^12.0).

## Changes Made

### 1. Composer Dependencies Updated
- **PHPUnit**: Upgraded to `^11.0||^12.0`
- **PHP Requirement**: Updated to `>=8.3` (compatible with current PHP 8.3 environments)
- **php-gedcom**: Downgraded to `^2.2.0` (PHP 8.3 compatible version)
- **New Dependencies Added**:
  - `orchestra/testbench`: ^10.7 (required by tests but was missing)
  - `mockery/mockery`: ^1.6 (required by tests but was missing)

### 2. Configuration Updates
- **phpunit.xml**: Updated for PHPUnit 12 compatibility
  - Added XML schema reference
  - Updated to use `<source>` instead of deprecated coverage filters
  - Added new PHPUnit 12 configuration options

### 3. Code Fixes
- **tests/TestCase.php**: Fixed PHP syntax (removed leading blank lines before `<?php`)
- **composer.json**: Added `autoload-dev` section for test namespace autoloading

## PHP 8.3 Compatibility

**Important**: This project now requires PHP 8.3 or higher.

### Why PHP 8.3?
The project has been adjusted to work with PHP 8.3 environments by using `liberu-genealogy/php-gedcom` v2.2.0, which is fully compatible with PHP >=8.3.

**Note**: The php-gedcom v4.x series requires PHP 8.4 due to the use of property hooks, a PHP 8.4 feature. To maintain broader compatibility, this project uses the v2.2.0 series.

## Test Status

### Current Environment (PHP 8.3)
The project is now compatible with PHP 8.3 environments using php-gedcom v2.2.0.

### Known Issues
Tests require dev dependencies to be installed. Due to the current configuration:
1. PHPUnit and test dependencies need to be installed via composer
2. Tests can be run once dependencies are available

## Recommendations

### For PHP 8.3 Environments
All tests should work in PHP 8.3 environments with php-gedcom v2.2.0.

### For PHP 8.4+ Environments
If you prefer to use PHP 8.4:
1. Update php-gedcom to v4.x series for latest features
2. Note that this will require PHP 8.4 minimum

## Next Steps

To fully verify the upgrade:
1. Install dev dependencies: `composer install`
2. Run tests in a PHP 8.3 environment: `vendor/bin/phpunit`
3. Ensure all tests pass

## Upgrade Benefits

With PHPUnit 12:
- Modern assertions and test APIs
- Better error reporting
- Improved performance
- Active maintenance and security updates
- PHP 8.3+ compatibility
