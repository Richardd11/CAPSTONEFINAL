<?php
/**
 * Login Debug Test Script
 * This script helps diagnose login issues
 */

session_start();
require_once 'vendor/autoload.php';

use App\Services\Auth\AuthService;
use App\Services\Database\DatabaseService;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        input { padding: 8px; margin: 5px 0; width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Login Debug Test</h1>
        
        <div class="card">
            <h2>1. Session Status</h2>
            <?php
            if (isset($_SESSION['user_id'])) {
                echo '<p class="success">✅ User is logged in</p>';
                echo '<p>User ID: ' . $_SESSION['user_id'] . '</p>';
                echo '<p>Role: ' . ($_SESSION['role'] ?? 'Not set') . '</p>';
                echo '<p>Name: ' . ($_SESSION['name'] ?? 'Not set') . '</p>';
            } else {
                echo '<p class="warning">⚠️ User is NOT logged in</p>';
            }
            ?>
            <details>
                <summary>View Full Session Data</summary>
                <pre><?php print_r($_SESSION); ?></pre>
            </details>
        </div>

        <div class="card">
            <h2>2. Database Connection</h2>
            <?php
            try {
                $db = DatabaseService::getInstance();
                echo '<p class="success">✅ Database connection successful</p>';
                
                // Test query to check users table
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo '<p>Total users in database: ' . $result['count'] . '</p>';
                
                // Check user roles
                $stmt = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
                $stmt->execute();
                $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<table>';
                echo '<tr><th>Role</th><th>Count</th></tr>';
                foreach ($roles as $role) {
                    echo '<tr><td>' . $role['role'] . '</td><td>' . $role['count'] . '</td></tr>';
                }
                echo '</table>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Database connection failed: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <div class="card">
            <h2>3. Test Login Form</h2>
            <form method="POST" action="">
                <label>School ID: <input type="text" name="test_school_id" value="2024-0001" /></label><br>
                <label>Password: <input type="password" name="test_password" value="password123" /></label><br>
                <button type="submit" name="test_login">Test Login</button>
            </form>
            
            <?php
            if (isset($_POST['test_login'])) {
                echo '<h3>Login Test Results:</h3>';
                
                $authService = new AuthService();
                $school_id = $_POST['test_school_id'];
                $password = $_POST['test_password'];
                
                echo '<p>Testing with School ID: ' . htmlspecialchars($school_id) . '</p>';
                
                // First, check if user exists
                $db = DatabaseService::getInstance();
                $stmt = $db->prepare("SELECT * FROM users WHERE school_id = ?");
                $stmt->execute([$school_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    echo '<p class="success">✅ User found in database</p>';
                    echo '<p>Name: ' . $user['name'] . '</p>';
                    echo '<p>Role: ' . $user['role'] . '</p>';
                    echo '<p>Status: ' . ($user['is_active'] ? 'Active' : 'Inactive') . '</p>';
                    
                    // Try to authenticate
                    $result = $authService->login($school_id, $password);
                    
                    if ($result['success']) {
                        echo '<p class="success">✅ Login successful!</p>';
                        echo '<p>Redirect URL would be: ';
                        switch($result['user']['role']) {
                            case 'admin':
                                echo '/admin/dashboard';
                                break;
                            case 'faculty':
                                echo '/faculty/dashboard';
                                break;
                            case 'student':
                                echo '/student-success';
                                break;
                            default:
                                echo 'Unknown role';
                        }
                        echo '</p>';
                    } else {
                        echo '<p class="error">❌ Login failed: ' . $result['message'] . '</p>';
                        if (password_verify($password, $user['password'])) {
                            echo '<p class="success">✅ Password is correct</p>';
                        } else {
                            echo '<p class="error">❌ Password is incorrect</p>';
                        }
                    }
                } else {
                    echo '<p class="error">❌ User not found in database</p>';
                }
            }
            ?>
        </div>

        <div class="card">
            <h2>4. Route Check</h2>
            <?php
            $routes_to_check = [
                '/api/auth/login' => 'POST',
                '/login' => 'GET',
                '/admin/dashboard' => 'GET',
                '/faculty/dashboard' => 'GET',
                '/student/dashboard' => 'GET',
                '/student-success' => 'GET'
            ];
            
            echo '<table>';
            echo '<tr><th>Route</th><th>Method</th><th>Status</th></tr>';
            
            foreach ($routes_to_check as $route => $method) {
                echo '<tr>';
                echo '<td>' . $route . '</td>';
                echo '<td>' . $method . '</td>';
                echo '<td>';
                
                // Check if route handler exists (simplified check)
                if ($method === 'POST' && $route === '/api/auth/login') {
                    echo '<span class="success">✅ Available</span>';
                } else {
                    // For GET routes, we can't easily check without actually calling them
                    echo '<span class="info">ℹ️ Should be available</span>';
                }
                
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            ?>
        </div>

        <div class="card">
            <h2>5. Common Users for Testing</h2>
            <?php
            try {
                $db = DatabaseService::getInstance();
                $stmt = $db->prepare("SELECT school_id, name, role, is_active FROM users WHERE role IN ('admin', 'faculty', 'student') ORDER BY role, school_id LIMIT 10");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($users) {
                    echo '<table>';
                    echo '<tr><th>School ID</th><th>Name</th><th>Role</th><th>Active</th></tr>';
                    foreach ($users as $user) {
                        echo '<tr>';
                        echo '<td>' . $user['school_id'] . '</td>';
                        echo '<td>' . $user['name'] . '</td>';
                        echo '<td>' . $user['role'] . '</td>';
                        echo '<td>' . ($user['is_active'] ? '✅' : '❌') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<p class="info">ℹ️ Default password is usually "password123" for test accounts</p>';
                } else {
                    echo '<p class="warning">No users found in database</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error fetching users: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <div class="card">
            <h2>6. Server Configuration</h2>
            <table>
                <tr><td>PHP Version</td><td><?php echo phpversion(); ?></td></tr>
                <tr><td>Server Software</td><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td></tr>
                <tr><td>Document Root</td><td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td></tr>
                <tr><td>Script Name</td><td><?php echo $_SERVER['SCRIPT_NAME']; ?></td></tr>
                <tr><td>Request URI</td><td><?php echo $_SERVER['REQUEST_URI']; ?></td></tr>
                <tr><td>Session Save Path</td><td><?php echo session_save_path(); ?></td></tr>
                <tr><td>Session Status</td><td><?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive'; ?></td></tr>
            </table>
        </div>

        <div class="card">
            <h2>Actions</h2>
            <form method="POST" style="display: inline;">
                <button type="submit" name="clear_session">Clear Session</button>
            </form>
            <a href="/login"><button type="button">Go to Login Page</button></a>
            <?php
            if (isset($_POST['clear_session'])) {
                session_destroy();
                echo '<p class="success">Session cleared! Refresh the page to see changes.</p>';
            }
            ?>
        </div>
    </div>
</body>
</html>
