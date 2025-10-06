<?php

session_start();

// Set timezone to Philippines (adjust as needed)
date_default_timezone_set('Asia/Manila');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\Auth\AuthController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\SubjectController;
use App\Controllers\Admin\AssignmentController;
use App\Controllers\Faculty\FacultyController;
use App\Controllers\Faculty\ExamController;

// Initialize router
$router = new Router();

// Create controllers
$authController = new AuthController();
// Protected controllers are instantiated lazily inside route handlers

// Debug information for routing issues
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
error_log("=== REQUEST DEBUG ===");
error_log("Method: " . $method);
error_log("Requested path: " . $currentPath);
error_log("Script name: " . $_SERVER['SCRIPT_NAME']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("====================");

// Root route - redirect to login
$router->get('/', function() {
    header('Location: /login');
    exit;
});

// Login page
$router->get('/login', function() use ($authController) {
    $authController->showLogin();
});

// API routes for authentication
$router->post('/api/auth/login', function() {
    $authController = new \App\Controllers\Auth\AuthController();
    $authController->login();
});

$router->post('/api/auth/logout', function() use ($authController) {
    $authController->logout();
});

// Admin Dashboard Routes
$router->get('/admin/dashboard', function() {
    (new AdminController())->dashboard();
});

$router->get('/admin/logout', function() {
    (new AdminController())->logout();
});

// Admin Subject Management Routes
$router->get('/admin/subjects', function() {
    (new AdminController())->subjects();
});

// Admin Assignment Management Routes
$router->get('/admin/assignments', function() {
    (new AdminController())->assignments();
});

// Faculty Dashboard Routes
$router->get('/faculty/dashboard', function() {
    (new FacultyController())->dashboard();
});

$router->get('/faculty/logout', function() {
    (new FacultyController())->logout();
});

// Faculty Exam Management Routes
$router->get('/faculty/create-exam', function() {
    (new ExamController())->createExam();
});

$router->post('/faculty/save-exam', function() {
    (new ExamController())->saveExam();
});

$router->post('/faculty/exams/save', function() {
    (new ExamController())->saveExam();
});

$router->post('/faculty/exams/update/{id}', function($id) {
    (new ExamController())->updateExam($id);
});

$router->post('/faculty/exams/autosave', function() {
    (new ExamController())->autoSave();
});

$router->post('/faculty/exams/validate', function() {
    (new ExamController())->validateExam();
});

$router->get('/faculty/exams', function() {
    (new ExamController())->listExams();
});

$router->get('/faculty/exam/{id}', function($id) {
    (new ExamController())->viewExam($id);
});

$router->get('/faculty/api/exam/{id}', function($id) {
    (new ExamController())->getExamApi($id);
});

$router->get('/faculty/exam/{id}/edit', function($id) {
    (new ExamController())->editExam($id);
});

$router->post('/faculty/exam/{id}/update', function($id) {
    (new ExamController())->updateExam($id);
});

$router->post('/faculty/exam/{id}/delete', function($id) {
    (new ExamController())->deleteExam($id);
});

// Faculty Exam Results Route
$router->get('/faculty/exam-results', function() {
    (new FacultyController())->examResults();
});

// Faculty API Routes for Exam Results
$router->get('/faculty/api/exams', function() {
    (new FacultyController())->getExamsApi();
});

$router->get('/faculty/api/exam/{id}/results', function($id) {
    (new FacultyController())->getExamResultsApi($id);
});

$router->get('/faculty/api/exam-attempt/{id}/details', function($id) {
    (new FacultyController())->getExamAttemptDetailsApi($id);
});

$router->get('/faculty/api/student-exam-details/{id}', function($id) {
    (new FacultyController())->getStudentExamDetailsApi($id);
});

$router->post('/faculty/api/exam-attempt/{id}/recalculate-score', function($id) {
    (new ExamController())->recalculateScore($id);
});

$router->post('/faculty/api/override-score', function() {
    error_log("=== OVERRIDE SCORE ROUTE CALLED ===");
    (new FacultyController())->overrideScore();
});

// Debug: Log all POST routes after registration
error_log("=== ALL POST ROUTES REGISTERED ===");
$reflection = new ReflectionClass($router);
$property = $reflection->getProperty('routes');
$property->setAccessible(true);
$routes = $property->getValue($router);
error_log("POST routes: " . json_encode(array_keys($routes['POST'] ?? [])));

// Faculty Student Management Routes
$router->get('/faculty/students', function() {
    (new \App\Controllers\Faculty\StudentController())->listStudents();
});

$router->get('/faculty/students/subject/{id}', function($id) {
    (new \App\Controllers\Faculty\StudentController())->getStudentsForSubject($id);
});

// Faculty Student Data API Routes
$router->get('/faculty/api/student/{id}/details', function($id) {
    (new \App\Controllers\Faculty\StudentDataController())->getStudentDetails($id);
});
$router->get('/faculty/api/student/{id}/progress', function($id) {
    (new \App\Controllers\Faculty\StudentDataController())->getStudentProgress($id);
});

// Student Dashboard Routes
$router->get('/student/dashboard', function() {
    (new \App\Controllers\Student\StudentDashboardController())->dashboard();
});

$router->get('/student-success', function() {
    (new \App\Controllers\Student\StudentDashboardController())->dashboard();
});

$router->get('/student/debug', function() {
    (new \App\Controllers\Student\StudentDashboardController())->debug();
});

$router->get('/student/logout', function() {
    (new \App\Controllers\Student\StudentDashboardController())->logout();
});

// Student Exam taking Routes
$router->get('/student/exam/{id}', function($id) {
    (new \App\Controllers\Student\ExamTakingController())->startExam($id);
});

// Legacy route for backward compatibility
$router->get('/student/exam/start/{id}', function($id) {
    (new \App\Controllers\Student\ExamTakingController())->startExam($id);
});

$router->post('/student/exam/submit', function() {
    (new \App\Controllers\Student\ExamTakingController())->submitExam();
});

$router->post('/student/exam/save-answer', function() {
    (new \App\Controllers\Student\ExamTakingController())->saveAnswer();
});

$router->get('/student/exam-result/{id}', function($id) {
    (new \App\Controllers\Student\ExamTakingController())->viewResult($id);
});

// Student success placeholder is now handled above

// Admin User Management Routes
$router->post('/admin/users/add', function() {
    (new AdminController())->addUser();
});

$router->post('/admin/users/add-student', function() {
    (new AdminController())->addStudent();
});

$router->post('/admin/users/edit-student', function() {
    (new AdminController())->editStudent();
});

$router->post('/admin/users/edit/{id}', function($id) {
    (new AdminController())->editUser($id);
});

$router->post('/admin/users/delete-student', function() {
    (new AdminController())->deleteStudent();
});

// Admin Faculty Management Routes
$router->post('/admin/users/add-faculty', function() {
    (new AdminController())->addFaculty();
});

$router->post('/admin/users/edit-faculty', function() {
    (new AdminController())->editFaculty();
});

$router->post('/admin/users/delete-faculty', function() {
    (new AdminController())->deleteFaculty();
});

$router->post('/admin/users/delete/{id}', function($id) {
    (new AdminController())->deleteUser($id);
});

// Admin API Routes for dashboard functionality
$router->get('/admin/statistics', function() {
    (new AdminController())->getStatistics();
});

$router->get('/admin/users', function() {
    (new AdminController())->getUsers();
});

$router->get('/admin/users/{id}', function($id) {
    (new AdminController())->getUser($id);
});

$router->post('/admin/users', function() {
    (new AdminController())->createUser();
});

$router->put('/admin/users/{id}', function($id) {
    (new AdminController())->updateUser($id);
});

$router->delete('/admin/users/{id}', function($id) {
    (new AdminController())->deleteUserApi($id);
});

// Subject Management Routes (AJAX and direct)

$router->post('/admin/subjects/add', function() {
    (new \App\Controllers\Admin\SubjectController())->addSubject();
});

$router->post('/admin/subjects/edit', function() {
    (new \App\Controllers\Admin\SubjectController())->editSubject();
});

$router->post('/admin/subjects/delete', function() {
    (new \App\Controllers\Admin\SubjectController())->deleteSubject();
});

$router->get('/admin/subjects/{id}', function($id) {
    (new SubjectController())->getSubject($id);
});

$router->get('/admin/subjects/search', function() {
    (new SubjectController())->searchSubjects();
});

$router->get('/admin/subjects/filter/year-level', function() {
    (new SubjectController())->getSubjectsByYearLevel();
});

$router->get('/admin/subjects/filter/semester', function() {
    (new SubjectController())->getSubjectsBySemester();
});

$router->get('/admin/subjects/refresh', function() {
    (new SubjectController())->refreshSubjects();
});

// Assignment Management Routes (AJAX only - embedded in dashboard)
$router->post('/admin/assignments/add', function() {
    (new AssignmentController())->addAssignment();
});

$router->post('/admin/assignments/edit', function() {
    (new AssignmentController())->editAssignment();
});

$router->post('/admin/assignments/delete', function() {
    (new AssignmentController())->deleteAssignment();
});

$router->get('/admin/assignments/{id}', function($id) {
    (new AssignmentController())->getAssignment($id);
});

$router->get('/admin/assignments/filter', function() {
    (new AssignmentController())->getAssignmentsByFilters();
});

$router->get('/admin/assignments/workload', function() {
    (new AssignmentController())->getFacultyWorkload();
});

$router->get('/admin/assignments/unassigned', function() {
    (new AssignmentController())->getUnassignedSubjects();
});

$router->get('/admin/assignments/refresh', function() {
    (new AssignmentController())->refreshAssignments();
});

$router->get('/admin/assignments/stats', function() {
    (new AssignmentController())->getAssignmentStats();
});

// Handle the request
$router->handleRequest();
?>