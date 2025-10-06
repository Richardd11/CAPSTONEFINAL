<?php

namespace App\Controllers\Student;

use App\Services\Auth\AuthService;
use App\Services\Exam\ExamService;
use App\Services\Student\StudentService;
use App\Core\View;
use Exception;

class StudentDashboardController
{
    private AuthService $authService;
    private ExamService $examService;
    private StudentService $studentService;
    private View $view;

    public function __construct(
        AuthService $authService = null,
        ExamService $examService = null,
        StudentService $studentService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->examService = $examService ?? new ExamService();
        $this->studentService = $studentService ?? new StudentService();
        $this->view = $view ?? new View();
    }

    public function dashboard(): void
    {
        $this->ensureStudent();
        $currentUser = $this->authService->getCurrentUserModel();
        
        // Check if user is properly authenticated
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }
        
        // Get available exams for this student based on year level and section
        $studentYearLevel = $currentUser->getYearLevel();
        $studentSection = $currentUser->getSection();
        
        // Debug: Log student details
        error_log("Student Year Level: " . $studentYearLevel);
        error_log("Student Section: " . $studentSection);
        error_log("Student ID: " . $currentUser->getUserId());
        error_log("Student Name: " . $currentUser->getFullName());
        
        // First, let's check if there are any exams in the database at all
        $allExams = $this->examService->getExamsByFilters([]);
        error_log("Total exams in database: " . count($allExams));
        
        if (!empty($allExams)) {
            foreach ($allExams as $exam) {
                error_log("Exam found: " . $exam->getTitle() . " - Year: " . $exam->getYearLevel() . ", Section: " . $exam->getSection() . ", Active: " . ($exam->getIsActive() ? 'Yes' : 'No'));
            }
        }
        
        $availableExams = $this->examService->getExamsForStudent(
            $studentYearLevel,
            $studentSection
        );
        
        // Debug: Log available exams count
        error_log("Available exams for student: " . count($availableExams));
        
        // Get student's exam attempts/history
        $examHistory = $this->examService->getStudentExamHistory($currentUser->getUserId());
        
        // Get student statistics
        $studentStats = [
            'total_exams_available' => count($availableExams),
            'exams_completed' => count(array_filter($examHistory, fn($attempt) => $attempt['status'] === 'completed')),
            'exams_pending' => count(array_filter($availableExams, function($exam) use ($examHistory) {
                return !in_array($exam['id'], array_column($examHistory, 'exam_id'));
            })),
            'average_score' => $this->calculateAverageScore($examHistory)
        ];
        
        $this->view->display('student.dashboard', [
            'student' => $currentUser,
            'availableExams' => $availableExams,
            'examHistory' => $examHistory,
            'studentStats' => $studentStats
        ]);
    }

    public function debug(): void
    {
        $this->ensureStudent();
        $currentUser = $this->authService->getCurrentUserModel();
        
        if (!$currentUser) {
            echo "No user found in session";
            return;
        }
        
        echo "<h2>Student Debug Information</h2>";
        echo "<p><strong>Student Name:</strong> " . htmlspecialchars($currentUser->getFullName()) . "</p>";
        echo "<p><strong>Student ID:</strong> " . $currentUser->getUserId() . "</p>";
        echo "<p><strong>School ID:</strong> " . htmlspecialchars($currentUser->getSchoolId()) . "</p>";
        echo "<p><strong>Year Level:</strong> " . htmlspecialchars($currentUser->getYearLevel()) . "</p>";
        echo "<p><strong>Section:</strong> " . htmlspecialchars($currentUser->getSection()) . "</p>";
        
        echo "<h3>All Exams in Database:</h3>";
        $allExams = $this->examService->getExamsByFilters([]);
        echo "<p><strong>Total Exams:</strong> " . count($allExams) . "</p>";
        
        if (!empty($allExams)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Title</th><th>Year Level</th><th>Section</th><th>Active</th><th>Subject</th><th>Faculty</th></tr>";
            foreach ($allExams as $exam) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($exam->getTitle()) . "</td>";
                echo "<td>" . htmlspecialchars($exam->getYearLevel()) . "</td>";
                echo "<td>" . htmlspecialchars($exam->getSection()) . "</td>";
                echo "<td>" . ($exam->getIsActive() ? 'Yes' : 'No') . "</td>";
                echo "<td>" . htmlspecialchars($exam->getSubjectCode() ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($exam->getFacultyName() ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        echo "<h3>Available Exams for This Student:</h3>";
        echo "<p><strong>Looking for exams with:</strong></p>";
        echo "<ul>";
        echo "<li>Year Level: <strong>" . htmlspecialchars($currentUser->getYearLevel()) . "</strong></li>";
        echo "<li>Section: <strong>" . htmlspecialchars($currentUser->getSection()) . "</strong></li>";
        echo "<li>Active: <strong>Yes</strong></li>";
        echo "</ul>";
        
        $availableExams = $this->examService->getExamsForStudent(
            $currentUser->getYearLevel(),
            $currentUser->getSection()
        );
        echo "<p><strong>Available Exams:</strong> " . count($availableExams) . "</p>";
        
        if (count($availableExams) === 0 && count($allExams) > 0) {
            echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0;'>";
            echo "<h4>❌ No Matching Exams Found</h4>";
            echo "<p>Possible reasons:</p>";
            echo "<ul>";
            echo "<li><strong>Year Level Mismatch:</strong> Check if exam year level exactly matches student year level</li>";
            echo "<li><strong>Section Mismatch:</strong> Check if exam section exactly matches student section</li>";
            echo "<li><strong>Exam Not Active:</strong> Faculty needs to activate the exam</li>";
            echo "<li><strong>Date Range:</strong> Exam might be outside the available date range</li>";
            echo "</ul>";
            echo "</div>";
        }
        
        if (!empty($availableExams)) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Title</th><th>Year Level</th><th>Section</th><th>Active</th></tr>";
            foreach ($availableExams as $exam) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($exam['title']) . "</td>";
                echo "<td>" . htmlspecialchars($exam['year_level']) . "</td>";
                echo "<td>" . htmlspecialchars($exam['section']) . "</td>";
                echo "<td>" . ($exam['is_active'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

    public function logout(): void
    {
        try {
            // Ensure session is started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // If confirmed, perform logout and go to login
            if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
                $this->authService->logout();
                header('Location: /login');
                exit;
            }

            // Debug: Log logout attempt
            error_log("Student logout attempt - User ID: " . ($_SESSION['user_id'] ?? 'none'));
            
            // Direct logout - clear session and redirect
            $logoutResult = $this->authService->logout();
            
            // Debug: Log logout result
            error_log("Logout result: " . json_encode($logoutResult));
            
            // Start new session for success message
            session_start();
            $_SESSION['success'] = 'You have been logged out successfully.';
            
            // Debug: Confirm redirect
            error_log("Redirecting to login page");
            
            header('Location: /login');
            exit;
            
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            
            // Fallback - manually clear session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            session_destroy();
            
            header('Location: /login');
            exit;
        }
    }

    private function ensureStudent(): void
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('student');
    }

    private function calculateAverageScore(array $examHistory): float
    {
        if (empty($examHistory)) {
            return 0.0;
        }

        $completedExams = array_filter($examHistory, fn($attempt) => $attempt['status'] === 'completed');
        
        if (empty($completedExams)) {
            return 0.0;
        }

        $totalScore = array_sum(array_column($completedExams, 'score'));
        return round($totalScore / count($completedExams), 1);
    }
}
