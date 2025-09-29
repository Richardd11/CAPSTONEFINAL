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
        
        // Get real data from database
        $students = $this->userService->getUsersByRole('student');
        $faculty = $this->userService->getUsersByRole('faculty');
        $allUsers = $this->userService->getAllUsers(); // Get all users for statistics
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
            'admin' => $currentUser, // Already an array from AuthService
            'users' => $allUsers, // All users for statistics and user management
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
        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            $this->authService->logout();
            header('Location: ' . $basePath . '/login');
            exit;
        }

        // Set session flag to show logout modal and redirect back to dashboard
        $_SESSION['show_logout_modal'] = true;
        header('Location: ' . $basePath . '/admin/dashboard');
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
     * Handle add user request
     */
    public function addUser()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->createUser($_POST);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle add student request
     */
    public function addStudent()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->createUser($_POST);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Redirect to admin dashboard
     */
    private function redirectToDashboard()
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $basePath = dirname($scriptName);
        header('Location: ' . $basePath . '/admin/dashboard');
        exit;
    }

    /**
     * Handle edit user request
     */
    public function editUser($userId)
    {
        $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        // Log the incoming data for debugging
        error_log("EditUser called with userId: {$userId}, data: " . json_encode($_POST));

        $result = $this->userService->updateUser($userId, $_POST);
        
        // Log the result for debugging
        error_log("UpdateUser result: " . json_encode($result));
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle edit student request
     */
    public function editStudent()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->updateUser($userId, $_POST);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Handle delete user request
     */
    public function deleteUser($userId)
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        if ($result['success']) {
            $this->showSuccess($result['message']);
        } else {
            $this->showError($result['message']);
        }
    }

    /**
     * Handle delete student request
     */
    public function deleteStudent()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            $this->showError('User ID is required.');
            return;
        }

        $result = $this->userService->deleteUser($userId);
        
        if ($result['success']) {
            // Store success message in session
            $_SESSION['success_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        } else {
            // Store error message in session
            $_SESSION['error_message'] = $result['message'];
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Add a new faculty member
     */
    public function addFaculty()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $data = [
            'school_id' => $_POST['school_id'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'role' => 'faculty',
            'password' => $_POST['password'] ?? ''
        ];

        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name'])) {
            $this->showError('School ID and Full Name are required');
            return;
        }

        try {
            $result = $this->userService->createUser($data);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member added successfully');
                $this->redirectToDashboard();
            } else {
                $this->showError($result['message']);
                $this->redirectToDashboard();
            }
        } catch (\Exception $e) {
            $this->showError('Error adding faculty member: ' . $e->getMessage());
            $this->redirectToDashboard();
        }
    }

    /**
     * Edit an existing faculty member
     */
    public function editFaculty()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        if (empty($userId)) {
            $_SESSION['error_message'] = 'User ID is required';
            $this->redirectToDashboard();
            return;
        }

        $data = [
            'user_id' => $userId,
            'school_id' => $_POST['school_id'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'role' => 'faculty'
        ];

        // Validate required fields
        if (empty($data['school_id']) || empty($data['full_name'])) {
            $_SESSION['error_message'] = 'School ID and Full Name are required';
            $this->redirectToDashboard();
            return;
        }

        try {
            $result = $this->userService->updateUser($data['user_id'] ?? null, $data);
            
            if ($result['success']) {
                // Store success message in session
                $_SESSION['success_message'] = 'Faculty member updated successfully';
                // Redirect back to dashboard
                $this->redirectToDashboard();
            } else {
                // Store error message in session
                $_SESSION['error_message'] = $result['message'];
                // Redirect back to dashboard
                $this->redirectToDashboard();
            }
        } catch (\Exception $e) {
            // Store error message in session
            $_SESSION['error_message'] = 'Error updating faculty member: ' . $e->getMessage();
            // Redirect back to dashboard
            $this->redirectToDashboard();
        }
    }

    /**
     * Delete a faculty member
     */
    public function deleteFaculty()
    {
    $this->ensureAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method');
            return;
        }

        $userId = $_POST['user_id'] ?? '';
        if (empty($userId)) {
            $this->showError('User ID is required');
            return;
        }

        try {
            $result = $this->userService->deleteUser($userId);
            
            if ($result['success']) {
                $this->showSuccess('Faculty member deleted successfully');
                $this->redirectToDashboard();
            } else {
                $this->showError($result['message']);
                $this->redirectToDashboard();
            }
        } catch (\Exception $e) {
            $this->showError('Error deleting faculty member: ' . $e->getMessage());
            $this->redirectToDashboard();
        }
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
}