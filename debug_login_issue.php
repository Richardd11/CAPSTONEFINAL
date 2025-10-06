<?php
/**
 * Debug Login Issue
 * This script helps identify the source of the "/api/auth/login was not found" error
 */

session_start();
require_once 'vendor/autoload.php';

use App\Core\Router;
use App\Controllers\Auth\AuthController;

echo "<h1>Login Issue Debug</h1>";

// Check current session
echo "<h2>Current Session:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if user is logged in
echo "<h2>Authentication Status:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
} else {
    echo "❌ User is not logged in<br>";
}

// Check router configuration
echo "<h2>Router Configuration:</h2>";
$router = new Router();
$authController = new AuthController();

// Add the login route
$router->post('/api/auth/login', function() use ($authController) {
    $authController->login();
});

// Get all routes
$routes = $router->getRoutes();
echo "<h3>Registered POST routes:</h3>";
echo "<pre>";
print_r($routes['POST'] ?? []);
echo "</pre>";

// Test path processing
echo "<h2>Path Processing Test:</h2>";
$testPaths = [
    '/api/auth/login',
    '/admin/dashboard',
    '/faculty/dashboard',
    '/student/dashboard'
];

foreach ($testPaths as $path) {
    echo "Path: $path<br>";
    
    // Simulate the router's path processing
    $processedPath = rtrim($path, '/');
    if (empty($processedPath)) {
        $processedPath = '/';
    }
    
    echo "Processed: $processedPath<br>";
    echo "Route exists: " . (isset($routes['POST'][$processedPath]) ? '✅ Yes' : '❌ No') . "<br><br>";
}

// Check server variables
echo "<h2>Server Variables:</h2>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'Not set') . "<br>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "<br>";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'Not set') . "<br>";

// Check if this is being called via AJAX
echo "<h2>Request Type:</h2>";
echo "X-Requested-With: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'Not set') . "<br>";
echo "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'Not set') . "<br>";

?>
