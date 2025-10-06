<?php
/**
 * Fix Login Issue Script
 * This script fixes common login problems in the exam system
 */

session_start();
require_once 'vendor/autoload.php';

use App\Services\Database\DatabaseService;

$message = '';
$error = '';

// Handle fix actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'reset_passwords':
            try {
                $db = DatabaseService::getInstance();
                $password = password_hash('password123', PASSWORD_DEFAULT);
                
                // Reset all user passwords to 'password123'
                $stmt = $db->prepare("UPDATE users SET password = ?");
                $stmt->execute([$password]);
                $affected = $stmt->rowCount();
                
                $message = "✅ Reset passwords for $affected users to 'password123'";
            } catch (Exception $e) {
                $error = "❌ Failed to reset passwords: " . $e->getMessage();
            }
            break;
            
        case 'activate_users':
            try {
                $db = DatabaseService::getInstance();
                
                // Activate all users
                $stmt = $db->prepare("UPDATE users SET is_active = 1");
                $stmt->execute();
                $affected = $stmt->rowCount();
                
                $message = "✅ Activated $affected users";
            } catch (Exception $e) {
                $error = "❌ Failed to activate users: " . $e->getMessage();
            }
            break;
            
        case 'create_test_users':
            try {
                $db = DatabaseService::getInstance();
                $password = password_hash('password123', PASSWORD_DEFAULT);
                
                // Create test users for each role
                $test_users = [
                    ['TEST-ADMIN', 'Test Admin', 'admin@test.com', 'admin'],
                    ['TEST-FACULTY', 'Test Faculty', 'faculty@test.com', 'faculty'],
                    ['TEST-STUDENT', 'Test Student', 'student@test.com', 'student']
                ];
                
                $created = 0;
                foreach ($test_users as $user) {
                    // Check if user already exists
                    $stmt = $db->prepare("SELECT id FROM users WHERE school_id = ?");
                    $stmt->execute([$user[0]]);
                    
                    if (!$stmt->fetch()) {
                        $stmt = $db->prepare("
                            INSERT INTO users (school_id, name, email, password, role, is_active, year_level, section)
                            VALUES (?, ?, ?, ?, ?, 1, ?, ?)
                        ");
                        
                        $year_level = $user[3] === 'student' ? '1st Year' : null;
                        $section = $user[3] === 'student' ? 'A' : null;
                        
                        $stmt->execute([
                            $user[0],
                            $user[1],
                            $user[2],
                            $password,
                            $user[3],
                            $year_level,
                            $section
                        ]);
                        $created++;
                    }
                }
                
                $message = "✅ Created $created test users (password: password123)";
            } catch (Exception $e) {
                $error = "❌ Failed to create test users: " . $e->getMessage();
            }
            break;
            
        case 'fix_routes':
            // Create a fixed AuthController
            $controllerPath = __DIR__ . '/src/App/Controllers/Auth/AuthController.php';
            $controllerContent = file_get_contents($controllerPath);
            
            // Check if student redirect is to /student-success
            if (strpos($controllerContent, "header('Location: /student-success');") !== false) {
                // Change to /student/dashboard
                $controllerContent = str_replace(
                    "header('Location: /student-success');",
                    "header('Location: /student/dashboard');",
                    $controllerContent
                );
                file_put_contents($controllerPath, $controllerContent);
                $message = "✅ Fixed student redirect route";
            } else {
                $message = "ℹ️ Student redirect route already fixed or different";
            }
            break;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Login Issues</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: black;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        button:hover {
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f2f2f2;
        }
        .test-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .test-form input {
            padding: 8px;
            margin: 5px;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Login Issues</h1>
        
        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Quick Fixes</h2>
            <p>Click these buttons to apply common fixes:</p>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="create_test_users">
                <button type="submit" class="btn-success">Create Test Users</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="reset_passwords">
                <button type="submit" class="btn-warning" onclick="return confirm('This will reset ALL user passwords to password123. Continue?')">Reset All Passwords</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="activate_users">
                <button type="submit" class="btn-primary">Activate All Users</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="fix_routes">
                <button type="submit" class="btn-primary">Fix Student Routes</button>
            </form>
        </div>
        
        <div class="card">
            <h2>Current Users</h2>
            <?php
            try {
                $db = DatabaseService::getInstance();
                $stmt = $db->prepare("
                    SELECT school_id, name, email, role, is_active, year_level, section 
                    FROM users 
                    ORDER BY role, school_id 
                    LIMIT 20
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($users) {
                    echo '<table>';
                    echo '<tr><th>School ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Year</th><th>Section</th></tr>';
                    foreach ($users as $user) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($user['school_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['role']) . '</td>';
                        echo '<td>' . ($user['is_active'] ? '✅' : '❌') . '</td>';
                        echo '<td>' . htmlspecialchars($user['year_level'] ?? '-') . '</td>';
                        echo '<td>' . htmlspecialchars($user['section'] ?? '-') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="warning">No users found in database</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">Error loading users: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <div class="card">
            <h2>Test Login Form</h2>
            <div class="test-form">
                <form action="/api/auth/login" method="POST">
                    <label>School ID: <input type="text" name="school_id" value="TEST-STUDENT"></label>
                    <label>Password: <input type="password" name="password" value="password123"></label>
                    <button type="submit" class="btn-primary">Test Login</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <h2>Navigation</h2>
            <a href="/login"><button class="btn-primary">Go to Login Page</button></a>
            <a href="/test_login_debug.php"><button class="btn-primary">Login Debug Page</button></a>
            <a href="/test_login_post.php"><button class="btn-primary">Test Login Post</button></a>
            <a href="/"><button class="btn-primary">Home</button></a>
        </div>
    </div>
</body>
</html>
