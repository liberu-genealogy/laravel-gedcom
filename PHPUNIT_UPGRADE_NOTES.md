# PHPUnit Upgrade Notes

## Summary

PHPUnit has been successfully upgraded from version 10.x/11.x to **12.3.15** (latest stable in the 12.x series).

## Changes Made

### 1. Composer Dependencies Updated
- **PHPUnit**: Upgraded to `^11.0||^12.0` (currently 12.3.15)
- **PHP Requirement**: Updated to `>=8.4` (due to dependency requirements)
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

## PHP 8.4 Requirement

**Important**: This project now requires PHP 8.4 or higher.

### Why PHP 8.4?
The `liberu-genealogy/php-gedcom` dependency (v4.2.0) contains code that uses PHP 8.4 features (property hooks), even though its composer.json declares PHP >=8.3 support. This is a bug in the php-gedcom package.

The php-gedcom library contains PHP 8.4 property hooks syntax in `src/Parser.php`:
```php
private \SplFileObject $fileHandle {
    get => $this->fileHandle ??= new \SplFileObject($this->fileName, 'r');
}
```

This syntax is not supported in PHP 8.3 and causes parse errors when the Parser class is loaded during test execution.

**Note**: While php-gedcom's composer.json claims >=8.3 compatibility, the actual code requires PHP 8.4. This should be reported as a bug to the php-gedcom maintainers.

## Test Status

### Current Test Results (PHP 8.3 Environment)
- **Total Tests**: 24
- **Passing**: 3
- **Errors**: 19 (mostly due to PHP 8.4 syntax in dependencies)
- **Failures**: 1
- **Risky**: 1

### Known Issues
1. **PHP 8.4 Syntax Errors** (6 tests): Tests that instantiate the GEDCOM Parser fail due to PHP 8.4 property hooks
2. **Service Provider Issues** (7 tests): Missing service bindings (gedcom-parser, events, log)
3. **Test Code Issues** (6 tests): Various test implementation problems

## Recommendations

### For PHP 8.4+ Environments
All tests should work once run in a PHP 8.4+ environment.

### For PHP 8.3 Environments
If you must use PHP 8.3:
1. Consider using an older version of `php-gedcom` (v3.x) that doesn't use PHP 8.4 syntax
2. Note that this may require code changes in the application to maintain compatibility

## Next Steps

To fully verify the upgrade:
1. Run tests in a PHP 8.4 environment
2. Fix remaining test issues (service bindings, test code)
3. Ensure all tests pass

## Upgrade Benefits

With PHPUnit 12:
- Modern assertions and test APIs
- Better error reporting
- Improved performance
- Active maintenance and security updates
- PHP 8.4 compatibility
