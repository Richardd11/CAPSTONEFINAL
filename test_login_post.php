<?php
/**
 * Test Login POST Request
 * This tests the actual login form submission
 */

session_start();

// If form is submitted, process it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Redirect to the login endpoint with POST data
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Testing Login...</title>
    </head>
    <body>
        <form id="loginForm" action="/api/auth/login" method="POST">
            <input type="hidden" name="school_id" value="<?php echo htmlspecialchars($_POST['school_id']); ?>">
            <input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password']); ?>">
        </form>
        <script>
            document.getElementById('loginForm').submit();
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .info {
            background: #f0f8ff;
            border-left: 4px solid #4a90e2;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
        }
        .info h3 {
            margin: 0 0 10px 0;
            color: #4a90e2;
        }
        .info p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔐 Test Login Form</h2>
        
        <div class="info">
            <h3>Common Test Accounts:</h3>
            <?php
            require_once 'vendor/autoload.php';
            use App\Services\Database\DatabaseService;
            
            try {
                $db = DatabaseService::getInstance();
                
                // Get one user of each type for testing
                $stmt = $db->prepare("
                    SELECT school_id, name, role 
                    FROM users 
                    WHERE is_active = 1 
                    AND role IN ('admin', 'faculty', 'student')
                    GROUP BY role
                    ORDER BY role
                    LIMIT 3
                ");
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($users) {
                    foreach ($users as $user) {
                        echo '<p><strong>' . ucfirst($user['role']) . ':</strong> ' . $user['school_id'] . '</p>';
                    }
                    echo '<p style="font-size: 12px; color: #999;">Default password: password123</p>';
                } else {
                    echo '<p>No test users found</p>';
                }
            } catch (Exception $e) {
                echo '<p>Unable to fetch test users</p>';
            }
            ?>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="school_id">School ID:</label>
                <input type="text" id="school_id" name="school_id" value="2024-0001" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="password123" required>
            </div>
            
            <button type="submit">Test Login →</button>
        </form>
    </div>
</body>
</html>
