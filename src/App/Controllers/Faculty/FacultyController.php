<?php

namespace App\Controllers\Faculty;

use App\Services\Auth\AuthService;
use App\Services\Assignment\AssignmentService;
use App\Services\Exam\ExamService;
use App\Services\Student\StudentService;
use App\Core\View;

class FacultyController
{
    private AuthService $authService;
    private AssignmentService $assignmentService;
    private ExamService $examService;
    private StudentService $studentService;
    private View $view;

    public function __construct(
        AuthService $authService = null,
        AssignmentService $assignmentService = null,
        ExamService $examService = null,
        StudentService $studentService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->assignmentService = $assignmentService ?? new AssignmentService();
        $this->examService = $examService ?? new ExamService();
        $this->studentService = $studentService ?? new StudentService();
        $this->view = $view ?? new View();
        // NOTE: authentication checks moved to per-action helper
    }

    public function dashboard(): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        // Check if user is properly authenticated
        if (!$currentUser) {
            // Redirect to login if user is not found
            header('Location: /login');
            exit;
        }
        
        // Get current academic year (you might want to make this configurable)
        $currentYear = date('Y');
        $academicYear = $currentYear . '-' . ($currentYear + 1);
        
        // Get assignments for this faculty member
        $assignments = $this->assignmentService->getAssignmentsByFilters([
            'faculty_id' => $currentUser->getUserId(),
            'status' => 'active'
        ]);
        
        // Get all subjects assigned to this faculty
        $assignedSubjects = [];
        foreach ($assignments as $assignment) {
            $assignedSubjects[] = [
                'assignment' => $assignment,
                'subject_id' => $assignment->getSubjectId(),
                'year_level' => $assignment->getYearLevel(),
                'section' => $assignment->getSection(),
                'academic_year' => $assignment->getAcademicYear(),
                'semester' => $assignment->getSemester()
            ];
        }

        // Get exam statistics for this faculty
        $examStats = $this->examService->getExamStats($currentUser->getUserId());
        
        // Get student statistics for this faculty
        $studentStats = $this->studentService->getStudentStats($currentUser->getUserId());
        
        $this->view->display('faculty.dashboard', [
            'faculty' => $currentUser,
            'assignments' => $assignments,
            'assignedSubjects' => $assignedSubjects,
            'academicYear' => $academicYear,
            'examStats' => $examStats,
            'studentStats' => $studentStats
        ]);
    }

    public function examResults(): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        // Check if user is properly authenticated
        if (!$currentUser) {
            header('Location: /login');
            exit;
        }
        
        // Get exam statistics for this faculty
        $examStats = $this->examService->getExamStats($currentUser->getUserId());
        
        // Get student statistics for this faculty
        $studentStats = $this->studentService->getStudentStats($currentUser->getUserId());
        
        $this->view->display('faculty.exam-results', [
            'faculty' => $currentUser,
            'examStats' => $examStats,
            'studentStats' => $studentStats
        ]);
    }

    public function getExamsApi(): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        if (!$currentUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            error_log("=== GETTING EXAMS FOR FACULTY ===");
            error_log("Faculty ID: " . $currentUser->getUserId());
            
            // Get exams for this faculty
            $exams = $this->examService->getExamsByFaculty($currentUser->getUserId());
            
            error_log("Found " . count($exams) . " exams for this faculty");
            
            // Format exams for frontend
            $formattedExams = [];
            foreach ($exams as $exam) {
                // Get actual student count who took this exam
                $studentCount = $this->examService->getExamStudentCount($exam->getId());
                
                error_log("Exam ID: " . $exam->getId() . " - Title: " . $exam->getTitle() . " - Student count: " . $studentCount);
                
                $formattedExams[] = [
                    'id' => $exam->getId(),
                    'title' => $exam->getTitle(),
                    'subject' => $exam->getSubjectCode() ?? 'Unknown Subject',
                    'date' => $exam->getCreatedAt(),
                    'students' => $studentCount
                ];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'exams' => $formattedExams
            ]);
        } catch (\Exception $e) {
            error_log("Error getting exams API: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading exams'
            ]);
        }
    }

    public function getExamResultsApi($examId): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        if (!$currentUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            // Check if filtering by specific student
            $studentId = $_GET['student_id'] ?? null;
            
            error_log("=== EXAM RESULTS API CALL ===");
            error_log("Exam ID received: " . $examId);
            error_log("Student ID filter: " . ($studentId ?? 'all'));
            error_log("Request URI: " . $_SERVER['REQUEST_URI']);
            
            // Get detailed exam results for this exam
            $results = $this->examService->getDetailedExamResults($examId, $studentId);
            
            error_log("Found " . count($results) . " exam results");
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'results' => $results,
                'debug' => [
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'result_count' => count($results)
                ]
            ]);
        } catch (\Exception $e) {
            error_log("Error getting exam results API: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading exam results'
            ]);
        }
    }

    public function getExamAttemptDetailsApi($attemptId): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        if (!$currentUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            error_log("=== EXAM ATTEMPT DETAILS API ===");
            error_log("Attempt ID: $attemptId");
            
            // Get detailed exam attempt information
            $details = $this->examService->getExamAttemptDetails($attemptId);
            
            error_log("Details returned: " . json_encode($details));
            
            if (!$details) {
                error_log("No details found for attempt ID: $attemptId");
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Exam attempt not found'
                ]);
                return;
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            error_log("Error getting exam attempt details API: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading exam attempt details'
            ]);
        }
    }

    public function getStudentExamDetailsApi($attemptId): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        if (!$currentUser) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        try {
            error_log("=== STUDENT EXAM DETAILS API ===");
            error_log("Attempt ID: $attemptId");
            
            // Get comprehensive student exam details with integrated accuracy fixes
            $details = $this->examService->getComprehensiveStudentExamDetails($attemptId);
            
            if (!$details) {
                error_log("No details found for attempt ID: $attemptId");
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Student exam details not found'
                ]);
                return;
            }
            
            // Verify the exam belongs to this faculty
            $exam = $this->examService->getExamById($details['exam_id']);
            if (!$exam || $exam->getFacultyId() != $currentUser->getUserId()) {
                error_log("Access denied for faculty ID: " . $currentUser->getUserId());
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Access denied'
                ]);
                return;
            }
            
            error_log("Details found for student: " . $details['student_name']);
            error_log("Accurate score: " . $details['score'] . "%");
            error_log("Correct answers: " . $details['correct_answers'] . "/" . $details['total_questions']);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $details
            ]);
        } catch (\Exception $e) {
            error_log("Error getting student exam details API: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error loading student exam details'
            ]);
        }
    }

    public function overrideScore(): void
    {
        error_log("=== OVERRIDE SCORE METHOD STARTED ===");
        
        try {
            $this->ensureFaculty();
            error_log("Faculty authentication passed");
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            error_log("Input received: " . json_encode($input));
            
            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid JSON input'
                ]);
                return;
            }
            
            // Validate required fields
            $requiredFields = ['attempt_id', 'question_id', 'new_score', 'reason'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || ($field === 'reason' && trim($input[$field]) === '')) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Missing required field: {$field}"
                    ]);
                    return;
                }
            }
            
            $attemptId = (int)$input['attempt_id'];
            $questionId = (int)$input['question_id'];
            $newScore = (float)$input['new_score'];
            $reason = trim($input['reason']);
            
            // Get current faculty user
            $currentUser = $this->authService->getCurrentUserModel();
            if (!$currentUser) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'User not authenticated'
                ]);
                return;
            }
            
            // Validate score range (get max points from question)
            $question = $this->examService->getQuestionById($questionId);
            if (!$question) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Question not found'
                ]);
                return;
            }
            
            $maxPoints = $question['points'] ?? 10;
            if ($newScore < 0 || $newScore > $maxPoints) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Score must be between 0 and {$maxPoints}"
                ]);
                return;
            }
            
            // Check if faculty has permission to override this exam
            error_log("Getting exam attempt for ID: $attemptId");
            $examAttempt = $this->examService->getExamAttemptById($attemptId);
            error_log("Exam attempt result: " . json_encode($examAttempt));
            
            if (!$examAttempt) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Exam attempt not found'
                ]);
                return;
            }
            
            // Verify faculty teaches this subject (optional security check)
            $exam = $this->examService->getExamById($examAttempt['exam_id']);
            if (!$exam) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Exam not found'
                ]);
                return;
            }
            
            // Save the override
            $result = $this->examService->overrideQuestionScore(
                $attemptId,
                $questionId,
                $newScore,
                $reason,
                $currentUser->getUserId()
            );
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Score overridden successfully',
                    'data' => [
                        'attempt_id' => $attemptId,
                        'question_id' => $questionId,
                        'new_score' => $newScore,
                        'reason' => $reason,
                        'overridden_by' => $currentUser->getFullName(),
                        'overridden_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to save score override'
                ]);
            }
            
        } catch (\Exception $e) {
            error_log("=== OVERRIDE SCORE ERROR ===");
            error_log("Error message: " . $e->getMessage());
            error_log("Error file: " . $e->getFile());
            error_log("Error line: " . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while overriding the score: ' . $e->getMessage()
            ]);
        }
    }

    public function logout(): void
    {
        // If confirmed, perform logout and go to login
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
            $this->authService->logout();
            header('Location: /login');
            return;
        }

        // Otherwise, ensure user is faculty and trigger the modal on dashboard
        $this->ensureFaculty();
        $_SESSION['show_logout_modal'] = true;
        header('Location: /faculty/dashboard');
        exit;
    }

    private function ensureFaculty(): void
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('faculty');
    }
}