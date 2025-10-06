<?php
session_start();
date_default_timezone_set('Asia/Manila');

echo "Testing minimal setup...\n";

try {
    require_once '../vendor/autoload.php';
    echo "✅ Autoloader loaded\n";
    
    // Test Router class
    if (class_exists('App\Core\Router')) {
        echo "✅ Router class exists\n";
        $router = new App\Core\Router();
        echo "✅ Router instantiated\n";
    } else {
        echo "❌ Router class not found\n";
    }
    
    // Test AuthController class
    if (class_exists('App\Controllers\Auth\AuthController')) {
        echo "✅ AuthController class exists\n";
    } else {
        echo "❌ AuthController class not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
