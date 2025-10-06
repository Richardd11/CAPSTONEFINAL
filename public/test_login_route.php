<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\Auth\AuthController;

echo "Testing login route registration...\n\n";

// Create router and register the same routes as index.php
$router = new Router();
$authController = new AuthController();

// Register the login route exactly as in index.php
$router->post('/api/auth/login', function() {
    $authController = new \App\Controllers\Auth\AuthController();
    $authController->login();
});

// Get all routes
$routes = $router->getRoutes();

echo "Registered POST routes:\n";
if (isset($routes['POST'])) {
    foreach ($routes['POST'] as $path => $callback) {
        echo "- '$path'\n";
    }
} else {
    echo "No POST routes found\n";
}

// Test the specific route
$testPath = '/api/auth/login';
echo "\nTesting route: '$testPath'\n";
echo "Route exists: " . (isset($routes['POST'][$testPath]) ? 'YES' : 'NO') . "\n";

// Simulate the router's path processing
$method = 'POST';
$originalPath = '/api/auth/login';

echo "\nSimulating router path processing:\n";
echo "Original path: '$originalPath'\n";

// Same logic as in Router::handleRequest()
$path = rtrim($originalPath, '/');
if (empty($path)) {
    $path = '/';
}

echo "Processed path: '$path'\n";
echo "Route match: " . (isset($routes[$method][$path]) ? 'YES' : 'NO') . "\n";

// Test if we can call the route
if (isset($routes[$method][$path])) {
    echo "\nTrying to execute route...\n";
    try {
        // Simulate POST data
        $_POST['school_id'] = 'ADMIN001';
        $_POST['password'] = 'password';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $callback = $routes[$method][$path];
        $callback();
        echo "Route executed successfully!\n";
    } catch (Exception $e) {
        echo "Error executing route: " . $e->getMessage() . "\n";
    }
} else {
    echo "Cannot execute route - not found\n";
}
?>
