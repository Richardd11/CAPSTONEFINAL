<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;

$router = new Router();

// Add the same route as in index.php
$router->post('/api/auth/login', function() {
    echo "Login route called";
});

// Get all routes
$routes = $router->getRoutes();

echo "All registered routes:\n";
print_r($routes);

echo "\nPOST routes:\n";
if (isset($routes['POST'])) {
    foreach ($routes['POST'] as $path => $callback) {
        echo "- '$path'\n";
    }
} else {
    echo "No POST routes found\n";
}

// Test if the route exists
$testPath = '/api/auth/login';
echo "\nTesting path: '$testPath'\n";
echo "Route exists: " . (isset($routes['POST'][$testPath]) ? 'YES' : 'NO') . "\n";

// Test exact string comparison
$registeredPaths = array_keys($routes['POST'] ?? []);
echo "\nExact string comparisons:\n";
foreach ($registeredPaths as $registeredPath) {
    echo "- '$registeredPath' === '$testPath': " . ($registeredPath === $testPath ? 'YES' : 'NO') . "\n";
    if ($registeredPath !== $testPath) {
        echo "  Length diff: " . strlen($registeredPath) . " vs " . strlen($testPath) . "\n";
        echo "  Hex comparison: " . bin2hex($registeredPath) . " vs " . bin2hex($testPath) . "\n";
    }
}
?>
