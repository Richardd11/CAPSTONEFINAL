<?php

namespace App\Controllers\Faculty;

use App\Services\Auth\AuthService;
use App\Services\Student\StudentService;
use App\Services\Exam\ExamService;
use App\DAO\Auth\UserDAO;

class StudentDataController
{
    private AuthService $authService;
    private StudentService $studentService;
    private ExamService $examService;
    private UserDAO $userDAO;

    public function __construct(
        AuthService $authService = null,
        StudentService $studentService = null,
        ExamService $examService = null,
        UserDAO $userDAO = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->studentService = $studentService ?? new StudentService();
        $this->examService = $examService ?? new ExamService();
        $this->userDAO = $userDAO ?? new UserDAO();
    }

    public function getStudentDetails($studentId): void
    {
        $this->ensureFaculty();
        
        header('Content-Type: application/json');
        
        try {
            // Get student basic information
            $student = $this->userDAO->findById($studentId);
            
            if (!$student) {
                echo json_encode(['error' => 'Student not found']);
                return;
            }

            // Get student's exam history and scores
            $examHistory = $this->examService->getStudentExamHistory($studentId);
            
            // Calculate statistics
            $totalExams = count($examHistory);
            $completedExams = count(array_filter($examHistory, fn($exam) => $exam['status'] === 'completed'));
            $averageScore = $this->calculateAverageScore($examHistory);
            
            $response = [
                'student_id' => $student->getSchoolId(),
                'full_name' => $student->getFullName(),
                'year_level' => $student->getYearLevel(),
                'section' => $student->getSection(),
                'role' => $student->getRole(),
                'created_at' => $student->getCreatedAt(),
                'stats' => [
                    'total_exams' => $totalExams,
                    'completed_exams' => $completedExams,
                    'average_score' => $averageScore
                ]
            ];
            
            echo json_encode($response);
            
        } catch (\Exception $e) {
            error_log("Error getting student details: " . $e->getMessage());
            echo json_encode(['error' => 'Failed to load student details']);
        }
    }

    public function getStudentProgress($studentId): void
    {
        $this->ensureFaculty();
        
        header('Content-Type: application/json');
        
        try {
            // Get student basic information
            $student = $this->userDAO->findById($studentId);
            
            if (!$student) {
                echo json_encode(['error' => 'Student not found']);
                return;
            }

            // Get detailed exam history with scores
            $examHistory = $this->examService->getStudentExamHistory($studentId);
            
            // Get available exams for this student
            $availableExams = $this->examService->getExamsForStudent(
                $student->getYearLevel(),
                $student->getSection()
            );
            
            // Calculate detailed statistics
            $completedExams = array_filter($examHistory, fn($exam) => $exam['status'] === 'completed');
            $pendingExams = count($availableExams) - count($completedExams);
            $averageScore = $this->calculateAverageScore($examHistory);
            
            $response = [
                'student_name' => $student->getFullName(),
                'stats' => [
                    'completed_exams' => count($completedExams),
                    'average_score' => $averageScore,
                    'pending_exams' => max(0, $pendingExams)
                ],
                'recent_exams' => array_slice($examHistory, -5) // Last 5 exams
            ];
            
            echo json_encode($response);
            
        } catch (\Exception $e) {
            error_log("Error getting student progress: " . $e->getMessage());
            echo json_encode(['error' => 'Failed to load student progress']);
        }
    }

    private function ensureFaculty(): void
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('faculty');
    }

    private function calculateAverageScore(array $examHistory): float
    {
        if (empty($examHistory)) {
            return 0.0;
        }

        $completedExams = array_filter($examHistory, fn($exam) => $exam['status'] === 'completed');
        
        if (empty($completedExams)) {
            return 0.0;
        }

        $totalScore = array_sum(array_column($completedExams, 'score'));
        return round($totalScore / count($completedExams), 1);
    }
}
