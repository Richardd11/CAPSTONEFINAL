<?php
// Test autoloader functionality
echo "Testing autoloader...\n";

try {
    require_once '../vendor/autoload.php';
    echo "✅ Autoloader included successfully\n";
    
    use App\Core\Router;
    echo "✅ Router use statement successful\n";
    
    $router = new Router();
    echo "✅ Router instantiated successfully\n";
    
    use App\Controllers\Auth\AuthController;
    echo "✅ AuthController use statement successful\n";
    
    $authController = new AuthController();
    echo "✅ AuthController instantiated successfully\n";
    
    echo "🎉 All classes loaded successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
