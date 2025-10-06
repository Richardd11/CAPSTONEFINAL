<?php
/**
 * Simple Router for PHP Built-in Server
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve static files directly
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
    return false; // Let PHP serve the file
}

// All other requests go to index.php
include 'index.php';
?>
