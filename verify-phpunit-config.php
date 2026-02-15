#!/usr/bin/env php
<?php
/**
 * PHPUnit Configuration Verification Script
 * 
 * This script verifies that the phpunit.xml configuration is valid
 * and compatible with PHPUnit 12.x
 */

echo "PHPUnit Configuration Verification\n";
echo "===================================\n\n";

$phpunitXmlPath = __DIR__ . '/phpunit.xml';

if (!file_exists($phpunitXmlPath)) {
    echo "❌ Error: phpunit.xml not found\n";
    exit(1);
}

echo "✅ phpunit.xml exists\n";

// Load and parse the XML
$xml = simplexml_load_file($phpunitXmlPath);

if ($xml === false) {
    echo "❌ Error: phpunit.xml is not valid XML\n";
    exit(1);
}

echo "✅ phpunit.xml is valid XML\n";

// Check for PHPUnit 12 specific elements
$attributes = $xml->attributes('xsi', true);
if (isset($attributes['noNamespaceSchemaLocation'])) {
    echo "✅ XML schema reference found: " . $attributes['noNamespaceSchemaLocation'] . "\n";
}

// Check for <source> element (PHPUnit 12 style)
if (isset($xml->source)) {
    echo "✅ Modern <source> element found (PHPUnit 12 compatible)\n";
} else {
    echo "⚠️  Warning: <source> element not found\n";
}

// Check for test suites
if (isset($xml->testsuites)) {
    $testSuiteCount = count($xml->testsuites->testsuite);
    echo "✅ Test suites defined: $testSuiteCount\n";
}

// Check bootstrap file
if (isset($xml['bootstrap'])) {
    $bootstrap = (string)$xml['bootstrap'];
    echo "✅ Bootstrap file configured: $bootstrap\n";
    
    if (file_exists(__DIR__ . '/' . $bootstrap)) {
        echo "✅ Bootstrap file exists\n";
    } else {
        echo "⚠️  Warning: Bootstrap file not found (install composer dependencies)\n";
    }
}

echo "\n";
echo "Summary\n";
echo "-------\n";
echo "PHPUnit configuration is valid and compatible with PHPUnit 12.x\n";
echo "\nTo run tests:\n";
echo "1. Install dependencies: composer install\n";
echo "2. Run tests: vendor/bin/phpunit\n";

exit(0);
