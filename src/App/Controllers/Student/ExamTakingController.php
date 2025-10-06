<?php

namespace App\Controllers\Student;

use App\Services\Auth\AuthService;
use App\Services\Exam\ExamService;
use App\Core\View;
use Exception;

class ExamTakingController
{
    private AuthService $authService;
    private ExamService $examService;
    private View $view;

    public function __construct(
        AuthService $authService = null,
        ExamService $examService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->examService = $examService ?? new ExamService();
        $this->view = $view ?? new View();
    }

    public function startExam($examId): void
    {
        $this->ensureStudent();
        $currentUser = $this->authService->getCurrentUserModel();
        
        // Get exam details
        $exam = $this->examService->getExamById($examId);
        if (!$exam) {
            $_SESSION['error'] = 'Exam not found.';
            header('Location: /student/dashboard');
            exit;
        }
        
        // Check if student is eligible for this exam
        if (!$this->examService->isStudentEligibleForExam($currentUser->getUserId(), $examId)) {
            $_SESSION['error'] = 'You are not eligible to take this exam.';
            header('Location: /student/dashboard');
            exit;
        }
        
        // Check if exam is already completed
        $existingAttempt = $this->examService->getStudentExamAttempt($currentUser->getUserId(), $examId);
        if ($existingAttempt && $existingAttempt['status'] === 'completed') {
            // Set session flag to show modal on dashboard
            $_SESSION['show_exam_completed_modal'] = [
                'exam_title' => $exam ? $exam->getTitle() : 'Unknown Exam',
                'exam_id' => $examId,
                'completed_date' => $existingAttempt['completed_at'] ?? $existingAttempt['created_at'] ?? 'recently'
            ];
            header('Location: /student-success');
            exit;
        }
        
        // Create or get exam attempt
        $attemptId = $this->examService->createOrGetExamAttempt($currentUser->getUserId(), $examId);
        
        // Get exam questions
        $questions = $this->examService->getExamQuestions($examId);
        
        // Debug: Log questions
        error_log("Exam ID: $examId - Questions found: " . count($questions));
        
        // Load options for multiple choice questions
        foreach ($questions as $question) {
            error_log("Question: " . $question->getQuestionText() . " - Type: " . $question->getQuestionType());
            if ($question->getQuestionType() === 'multiple_choice') {
                $options = $this->examService->getQuestionOptions($question->getId());
                
                // Convert option objects to arrays for the view
                $optionsArray = [];
                foreach ($options as $option) {
                    $optionsArray[] = $option->toArray();
                }
                
                $question->setOptions($optionsArray);
                error_log("Options loaded for question: " . count($optionsArray));
            }
        }
        
        // Get existing answers if any
        $existingAnswers = $this->examService->getStudentAnswers($attemptId);
        
        $this->view->display('student.take-exam', [
            'student' => $currentUser,
            'exam' => $exam->toArray(),
            'questions' => $questions,
            'attemptId' => $attemptId,
            'existingAnswers' => $existingAnswers,
            'timeRemaining' => $this->calculateTimeRemaining($exam, $existingAttempt)
        ]);
    }

    public function submitExam(): void
    {
        $this->ensureStudent();
        $currentUser = $this->authService->getCurrentUserModel();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
                return;
            }
            header('Location: /student/dashboard');
            exit;
        }
        
        $attemptId = $_POST['attempt_id'] ?? null;
        $answers = $_POST['answers'] ?? [];
        
        if (!$attemptId) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid exam attempt']);
                return;
            }
            $_SESSION['error'] = 'Invalid exam attempt.';
            header('Location: /student/dashboard');
            exit;
        }
        
        try {
            // Save answers and calculate score
            $result = $this->examService->submitExamAnswers($attemptId, $answers, $currentUser->getUserId());
            
            if ($result['success']) {
                // Mark exam as completed in session for immediate UI update
                $_SESSION['exam_completed'] = true;
                $_SESSION['success'] = 'Exam submitted successfully! Your answers have been recorded.';
                
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse([
                        'success' => true, 
                        'message' => 'Exam submitted successfully!',
                        'redirect' => '/student-success'
                    ]);
                    return;
                }
                header('Location: /student/exam-result/' . $attemptId);
            } else {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse(['success' => false, 'message' => $result['message'] ?? 'Failed to submit exam']);
                    return;
                }
                $_SESSION['error'] = $result['message'] ?? 'Failed to submit exam.';
                header('Location: /student/dashboard');
            }
        } catch (Exception $e) {
            error_log("Exam submission error: " . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->jsonResponse(['success' => false, 'message' => 'An error occurred while submitting the exam']);
                return;
            }
            $_SESSION['error'] = 'An error occurred while submitting the exam.';
            header('Location: /student/dashboard');
        }
        
        exit;
    }

    public function saveAnswer(): void
    {
        $this->ensureStudent();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        $attemptId = $_POST['attempt_id'] ?? null;
        $questionId = $_POST['question_id'] ?? null;
        $answer = $_POST['answer'] ?? '';
        
        if (!$attemptId || !$questionId) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        try {
            $result = $this->examService->saveStudentAnswer($attemptId, $questionId, $answer);
            echo json_encode(['success' => $result, 'message' => $result ? 'Answer saved' : 'Failed to save answer']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error saving answer']);
        }
        
        exit;
    }
    public function viewResult($attemptId): void
    {
        $this->ensureStudent();
        $currentUser = $this->authService->getCurrentUserModel();
        
        // Get exam attempt
        $attempt = $this->examService->getExamAttemptById($attemptId);
        if (!$attempt || $attempt['student_id'] !== $currentUser->getUserId()) {
            $_SESSION['error'] = 'Exam result not found.';
            header('Location: /student/dashboard');
            exit;
        }
        
        // Get exam details
        $exam = $this->examService->getExamById($attempt['exam_id']);
        
        // Get detailed results
        $results = $this->examService->getDetailedExamResults($attemptId);
        
        $this->view->display('student.exam-result', [
            'student' => $currentUser,
            'exam' => $exam ? $exam->toArray() : [],
            'attempt' => $attempt,
            'results' => $results
        ]);
    }

    private function ensureStudent(): void
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('student');
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function calculateTimeRemaining($exam, $existingAttempt): int
    {
        // Get time limit with fallback
        $timeLimit = $exam->getTimeLimit() ?? 60; // Default 60 minutes if null
        
        if (!$existingAttempt) {
            return $timeLimit * 60; // Convert to seconds
        }
        
        $startTime = strtotime($existingAttempt['started_at']);
        $currentTime = time();
        $elapsedSeconds = $currentTime - $startTime;
        $totalSeconds = $timeLimit * 60;
        
        return max(0, $totalSeconds - $elapsedSeconds);
    }
}
