<?php
/**
 * Development Server Starter
 * 
 * This script starts a PHP built-in development server
 * pointing to the correct public directory
 */

$host = '127.0.0.1';
$port = 8000;
$publicDir = __DIR__ . '/public';

echo "🚀 Starting Exam Management System Development Server\n";
echo "====================================================\n\n";

// Check if public directory exists
if (!is_dir($publicDir)) {
    echo "❌ Error: Public directory not found at: $publicDir\n";
    exit(1);
}

// Check if index.php exists
if (!file_exists($publicDir . '/index.php')) {
    echo "❌ Error: index.php not found in public directory\n";
    exit(1);
}

echo "📁 Document Root: $publicDir\n";
echo "🌐 Server Address: http://$host:$port\n";
echo "🔗 Login URL: http://$host:$port/login\n\n";

echo "🔐 Default Login Credentials:\n";
echo "   Admin: ADMIN001 / password\n";
echo "   Faculty: FAC001 / password\n";
echo "   Student: 2022-001 / password\n\n";

echo "📝 Server Log:\n";
echo "==============\n";

// Start the server
$command = "php -S $host:$port -t \"$publicDir\"";
echo "Starting server with command: $command\n\n";

// Execute the server command
passthru($command);
?>
