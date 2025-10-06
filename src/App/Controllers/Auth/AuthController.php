<?php

namespace App\Controllers\Auth;

use App\Services\Auth\AuthService;
use App\Core\View;

class AuthController
{
    private $authService;
    private $view;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->view = new View();
    }

    /**
     * Show login page
     */
    public function showLogin()
    {
        // If user is already logged in, redirect to appropriate dashboard
        if ($this->authService->isAuthenticated()) {
            $user = $this->authService->getCurrentUser();
            $role = $user['role'] ?? null;
            // Guard against invalid or missing roles to avoid redirect loops
            if (!in_array($role, ['admin', 'faculty', 'student'], true)) {
                $this->authService->logout();
                $this->view->display('auth.login', ['error' => 'Your session role is invalid. Please log in again.']);
                return;
            }
            $this->redirectToDashboard($role);
            return;
        }

        $this->view->display('auth.login');
    }

    /**
     * Handle login request
     */
    public function login()
    {
        // Enhanced logging for debugging
        error_log("=== LOGIN REQUEST DEBUG ===");
        error_log("Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("URI: " . $_SERVER['REQUEST_URI']);
        error_log("Headers: " . json_encode(getallheaders()));
        error_log("POST data: " . json_encode($_POST));
        error_log("Session before: " . json_encode($_SESSION));
        error_log("===========================");
        
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showLoginError('Invalid request method.');
            return;
        }

        try {
            // Get POST data
            $school_id = $_POST['school_id'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($school_id) || empty($password)) {
                $this->showLoginError('School ID and password are required.');
                return;
            }

            // Use AuthService for login
            $result = $this->authService->login($school_id, $password);

            if ($result['success']) {
                // Redirect based on role
                $this->redirectToDashboard($result['user']['role']);
            } else {
                // Show error message
                $this->showLoginError($result['message']);
            }
        } catch (\Exception $e) {
            // Handle any unexpected errors
            $this->showLoginError('An error occurred during login.');
        }
    }

    /**
     * Show login page with error
     */
    private function showLoginError($message)
    {
        $this->view->display('auth.login', ['error' => $message]);
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        header('Content-Type: application/json');

        $result = $this->authService->logout();

        echo json_encode([
            'status' => 'success',
            'message' => $result['message']
        ]);
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard($role)
    {
        switch ($role) {
            case 'admin':
                header('Location: /admin/dashboard');
                exit;
            case 'faculty':
                header('Location: /faculty/dashboard');
                exit;
            case 'student':
                header('Location: /student-success');
                exit;
            default:
                // Unknown role: clear session to avoid loops, then redirect to login
                $this->authService->logout();
                header('Location: /login');
                exit;
        }
    }
}