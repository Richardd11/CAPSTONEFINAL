<?php
/**
 * Router for PHP Built-in Server
 * This file ensures all requests are properly routed through index.php
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the request is for a static file that exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Let the built-in server handle static files
}

// Ensure we're in the correct directory for autoloader
$originalDir = getcwd();
chdir(__DIR__);

// Set SCRIPT_NAME to index.php for consistent routing
$_SERVER['SCRIPT_NAME'] = '/index.php';

// For all other requests, route through index.php
try {
    require_once 'index.php';
} catch (Exception $e) {
    // Restore original directory on error
    chdir($originalDir);
    throw $e;
}
?>
