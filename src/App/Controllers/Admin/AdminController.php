<?php

namespace App\Controllers\Admin;

use App\Services\Auth\AuthService;
use App\Services\User\UserService;
use App\Services\Subject\SubjectService;
use App\Services\Assignment\AssignmentService;
use App\Core\View;

class AdminController
{
    private $authService;
    private $userService;
    private $subjectService;
    private $assignmentService;
    private $view;
    private $router;

    public function __construct(
        AuthService $authService = null,
        UserService $userService = null,
        SubjectService $subjectService = null,
        AssignmentService $assignmentService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->userService = $userService ?? new UserService();
        $this->subjectService = $subjectService ?? new SubjectService();
        $this->assignmentService = $assignmentService ?? new AssignmentService();
        $this->router = new \App\Core\Router();
        $this->view = $view ?? new View();
        
    // NOTE: authentication and role checks moved out of constructor
    // to avoid blocking actions that should be callable without
    // a fully valid session (for example, logout confirmation).
    }

    /**
     * Ensure the current user is authenticated and has admin role.
     * This is called at the start of protected actions.
     */
    private function ensureAdmin()
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('admin');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $this->ensureAdmin();

        $currentUser = $this->authService->getCurrentUser();
        
        // Get sorted users from service (business logic moved to service)
        $allUsers = $this->userService->getAllUsersSorted();
        $preparedUsers = $this->userService->prepareUsersForView($allUsers);
        
        // Get other data
        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');
        $subjects = $this->subjectService->getAllSubjects();
        $assignments = $this->assignmentService->getAllAssignments();
        
        // Convert User objects to arrays for view compatibility
        $studentsArray = $this->userService->usersToArray($students);
        $facultyArray = $this->userService->usersToArray($faculty);
        
        // Convert SubjectAssignment objects to arrays for JavaScript compatibility
        $assignmentsArray = [];
        foreach ($assignments as $assignment) {
            $assignmentsArray[] = $assignment->toArray();
        }
        
        $data = [
            'admin' => $currentUser,
            'users' => $preparedUsers, // Pre-sorted and prepared for view
            'students' => $studentsArray,
            'faculty' => $facultyArray,
            'subjects' => $subjects,
            'assignments' => $assignmentsArray,
            'yearSections' => $this->getYearSections($studentsArray),
            'yearLevels' => $this->subjectService->getYearLevels(),
            'semesters' => $this->subjectService->getSemesters(),
            'assignmentYearLevels' => $this->assignmentService->getYearLevels(),
            'assignmentSections' => $this->assignmentService->getSections(),
            'academicYears' => $this->assignmentService->getAcademicYears(),
            'assignmentSemesters' => $this->assignmentService->getSemesters(),
            'assignmentStatuses' => $this->assignmentService->getAssignmentStatuses()
        ];
        
        $this->view->display('admin.dashboard', $data);
    }

    /**
     * Show subjects management page
     */
    public function subjects()
    {
        $this->ensureAdmin();
        
        try {
            $subjects = $this->subjectService->getAllSubjects();
            $data = [
                'subjects' => $subjects,
                'title' => 'Subject Management'
            ];
            
            $this->view->display('admin.subjects', $data);
        } catch (\Exception $e) {
            error_log("Error loading subjects: " . $e->getMessage());
            $this->view->display('admin.subjects', ['subjects' => [], 'error' => 'Failed to load subjects']);
        }
    }

    /**
     * Show assignments management page
     */
    public function assignments()
    {
        $this->ensureAdmin();
        
        try {
            $assignments = $this->assignmentService->getAllAssignments();
            $subjects = $this->subjectService->getAllSubjects();
            $faculty = $this->userService->getUsersByRole('faculty');
            
            // Convert faculty User objects to arrays for view compatibility
            $facultyArray = $this->userService->usersToArray($faculty);
            
            $data = [
                'assignments' => $assignments,
                'subjects' => $subjects,
                'faculty' => $facultyArray,
                'title' => 'Faculty Assignments'
            ];
            
            $this->view->display('admin.assignments', $data);
        } catch (\Exception $e) {
            error_log("Error loading assignments: " . $e->getMessage());
            $this->view->display('admin.assignments', [
                'assignments' => [], 
                'subjects' => [], 
                'faculty' => [], 
                'error' => 'Failed to load assignments'
            ]);
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            $this->authService->logout();
            header('Location: /login');
            exit;
        }

        // Set session flag to show logout modal and redirect back to dashboard
        $_SESSION['show_logout_modal'] = true;
        header('Location: /admin/dashboard');
        exit;
    }





    /**
     * Get year-section combinations with counts
     */
    private function getYearSections($students)
    {
        $yearSections = [];
        
        foreach ($students as $student) {
            $key = $student['year_level'] . ' ' . $student['section'];
            if (!isset($yearSections[$key])) {
                $yearSections[$key] = 0;
            }
            $yearSections[$key]++;
        }
        
        return $yearSections;
    }

    /**
     * Handle add user request (unified method for all user types)
     * Returns JSON for AJAX requests, redirects for form submissions
     */
    public function addUser()
    {
        $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        error_log("=== ADD USER REQUEST ===");
        error_log("POST data: " . json_encode($_POST));
        
        $result = $this->userService->createUser($_POST);
        
        error_log("Result: " . json_encode($result));
        
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Also check if Accept header prefers JSON
        $acceptsJson = isset($_SERVER['HTTP_ACCEPT']) && 
                       strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        
        // Return JSON for AJAX/API requests
        if ($isAjax || $acceptsJson || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            return;
        }
        
        // Otherwise, use traditional redirect
        $this->handleUserOperationResult($result);
    }

    /**
     * Handle add student request (delegates to addUser for consistency)
     */
    public function addStudent()
    {
        $this->addUser();
    }

    /**
     * Handle user operation result (unified response handling)
     */
    private function handleUserOperationResult($result)
    {
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
        $this->redirectToDashboard();
    }

    /**
     * Redirect to admin dashboard
     */
    private function redirectToDashboard()
    {
        header('Location: /admin/dashboard');
        exit;
    }

    /**
     * Handle edit user request (unified method for all user types)
     * Returns JSON for AJAX requests, redirects for form submissions
     */
    public function editUser($userId = null)
    {
        $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        // Get user ID from parameter or POST data
        $userId = $userId ?? ($_POST['user_id'] ?? null);
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->updateUser($userId, $_POST);
        
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Return JSON for AJAX/API requests
        if ($isAjax || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            return;
        }
        
        $this->handleUserOperationResult($result);
    }

    /**
     * Handle edit student request (delegates to editUser for consistency)
     */
    public function editStudent()
    {
        $this->editUser();
    }

    /**
     * Handle delete user request (unified method for all user types)
     * Returns JSON for AJAX requests, redirects for form submissions
     */
    public function deleteUser($userId = null)
    {
        $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        // Get user ID from parameter or POST data
        $userId = $userId ?? ($_POST['user_id'] ?? null);
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        // Check if this is an AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Return JSON for AJAX/API requests
        if ($isAjax || strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            return;
        }
        
        $this->handleUserOperationResult($result);
    }

    /**
     * Handle delete student request (delegates to deleteUser for consistency)
     */
    public function deleteStudent()
    {
        $this->deleteUser();
    }

    /**
     * Add a new faculty member (delegates to addUser for consistency)
     */
    public function addFaculty()
    {
        // Ensure role is set to faculty
        $_POST['role'] = 'faculty';
        $this->addUser();
    }

    /**
     * Edit an existing faculty member (delegates to editUser for consistency)
     */
    public function editFaculty()
    {
        // Ensure role is set to faculty
        $_POST['role'] = 'faculty';
        $this->editUser();
    }

    /**
     * Delete a faculty member (delegates to deleteUser for consistency)
     */
    public function deleteFaculty()
    {
        $this->deleteUser();
    }

    /**
     * Show success message
     */
    private function showSuccess($message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Show error message
     */
    private function showError($message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }

    /**
     * API: Get statistics for dashboard
     */
    public function getStatistics()
    {
        $this->ensureAdmin();
        
        try {
            $allUsers = $this->userService->getAllUsers();
            $students = $this->userService->getUsersByRole('student');
            $faculty = $this->userService->getUsersByRole('faculty');
            $admins = $this->userService->getUsersByRole('admin');
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'totalUsers' => count($allUsers),
                'students' => count($students),
                'faculty' => count($faculty),
                'admins' => count($admins)
            ]);
        } catch (\Exception $e) {
            $this->showError('Failed to get statistics: ' . $e->getMessage());
        }
    }

    /**
     * API: Get all users
     */
    public function getUsers()
    {
        $this->ensureAdmin();
        
        try {
            $users = $this->userService->getAllUsersSorted();
            $preparedUsers = $this->userService->prepareUsersForView($users);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'users' => $preparedUsers
            ]);
        } catch (\Exception $e) {
            $this->showError('Failed to get users: ' . $e->getMessage());
        }
    }

    /**
     * API: Get single user
     */
    public function getUser($id)
    {
        $this->ensureAdmin();
        
        try {
            $user = $this->userService->getUserById($id);
            if (!$user) {
                $this->showError('User not found');
                return;
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'user' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            $this->showError('Failed to get user: ' . $e->getMessage());
        }
    }

    /**
     * API: Create user
     */
    public function createUser()
    {
        $this->ensureAdmin();
        
        try {
            // Get JSON data or form data
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $result = $this->userService->createUser($data);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Exception $e) {
            $this->showError('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * API: Update user
     */
    public function updateUser($id)
    {
        $this->ensureAdmin();
        
        try {
            // Get JSON data or form data
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $result = $this->userService->updateUser($id, $data);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Exception $e) {
            $this->showError('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * API: Delete user
     */
    public function deleteUserApi($id)
    {
        $this->ensureAdmin();
        
        try {
            $result = $this->userService->deleteUser($id);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Exception $e) {
            $this->showError('Failed to delete user: ' . $e->getMessage());
        }
    }
}