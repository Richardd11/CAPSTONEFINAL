<?php

/**
 * Test Runner Script for Faculty Module
 * 
 * This script runs all unit tests for the faculty module following TDD principles.
 * It provides a simple interface to run tests and view results.
 */

echo "🧪 Faculty Module Test Suite\n";
echo "===========================\n\n";

// Check if PHPUnit is available
if (!file_exists('vendor/bin/phpunit') && !file_exists('vendor/bin/phpunit.bat')) {
    echo "❌ PHPUnit not found. Please install it first:\n";
    echo "   composer install\n\n";
    exit(1);
}

// Determine the correct PHPUnit executable for Windows
$phpunit = 'vendor\\bin\\phpunit.bat';

echo "🔍 Running Unit Tests...\n\n";

// Run different test suites
$testSuites = [
    'Models' => 'tests/Unit/Models',
    'Services' => 'tests/Unit/Services', 
    'Controllers' => 'tests/Unit/Controllers',
    'DAOs' => 'tests/Unit/DAO'
];

$totalTests = 0;
$totalFailures = 0;

foreach ($testSuites as $suiteName => $path) {
    if (is_dir($path)) {
        echo "📋 Running $suiteName Tests:\n";
        echo str_repeat('-', 40) . "\n";
        
        $command = "$phpunit --colors=always --testdox $path";
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        foreach ($output as $line) {
            echo "  $line\n";
        }
        
        if ($returnCode === 0) {
            echo "✅ $suiteName tests passed!\n\n";
        } else {
            echo "❌ $suiteName tests failed!\n\n";
            $totalFailures++;
        }
        
        $totalTests++;
    }
}

// Summary
echo "📊 Test Summary:\n";
echo "================\n";
echo "Total Test Suites: $totalTests\n";
echo "Passed: " . ($totalTests - $totalFailures) . "\n";
echo "Failed: $totalFailures\n\n";

if ($totalFailures === 0) {
    echo "🎉 All tests passed! Your faculty module is working correctly.\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please check the output above for details.\n";
    exit(1);
}
