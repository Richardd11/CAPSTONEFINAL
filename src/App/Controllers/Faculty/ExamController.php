<?php

namespace App\Controllers\Faculty;

use App\Services\Auth\AuthService;
use App\Services\Assignment\AssignmentService;
use App\Services\Exam\ExamService;
use App\Core\View;

class ExamController
{
    private AuthService $authService;
    private AssignmentService $assignmentService;
    private ExamService $examService;
    private View $view;

    public function __construct(
        AuthService $authService = null,
        AssignmentService $assignmentService = null,
        ExamService $examService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->assignmentService = $assignmentService ?? new AssignmentService();
        $this->examService = $examService ?? new ExamService();
        $this->view = $view ?? new View();
    }

    public function createExam(): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        // Get assignments for this faculty member
        $assignments = $this->assignmentService->getAssignmentsByFilters([
            'faculty_id' => $currentUser->getUserId(),
            'status' => 'active'
        ]);
        
        $this->view->display('faculty.create-exam', [
            'faculty' => $currentUser,
            'assignments' => $assignments
        ]);
    }

    public function saveExam(): void
    {
        $this->ensureFaculty();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $currentUser = $this->authService->getCurrentUserModel();
        
        // Get JSON input
        $input = file_get_contents('php://input');
        $examData = json_decode($input, true);
        
        if (!$examData) {
            $this->showError('Invalid exam data.');
            return;
        }

        // Add faculty ID to exam data
        $examData['faculty_id'] = $currentUser->getUserId();
        
        $result = $this->examService->createExam($examData);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function listExams(): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        $exams = $this->examService->getExamsByFaculty($currentUser->getUserId());
        
        // Load question count for each exam
        foreach ($exams as $exam) {
            $questions = $this->examService->getExamQuestions($exam->getId());
            $exam->setQuestions($questions);
        }
        
        $this->view->display('faculty.exams', [
            'faculty' => $currentUser,
            'exams' => $exams
        ]);
    }

    public function editExam($examId): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        $exam = $this->examService->getExamById($examId);
        
        if (!$exam || $exam->getFacultyId() != $currentUser->getUserId()) {
            $this->showError('Exam not found or access denied.');
            return;
        }

        // Get assignments for this faculty member
        $assignments = $this->assignmentService->getAssignmentsByFilters([
            'faculty_id' => $currentUser->getUserId(),
            'status' => 'active'
        ]);

        // Get exam questions
        $questions = $this->examService->getExamQuestions($examId);
        
        // Load options for each question
        foreach ($questions as $question) {
            if ($question->getQuestionType() === 'multiple_choice') {
                $options = $this->examService->getQuestionOptions($question->getId());
                $question->setOptions($options);
            }
        }
        
        $this->view->display('faculty.edit-exam', [
            'faculty' => $currentUser,
            'exam' => $exam,
            'questions' => $questions,
            'assignments' => $assignments
        ]);
    }

    public function updateExam($examId): void
    {
        $this->ensureFaculty();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $currentUser = $this->authService->getCurrentUserModel();
        
        // Verify ownership
        $exam = $this->examService->getExamById($examId);
        if (!$exam || $exam->getFacultyId() != $currentUser->getUserId()) {
            $this->showError('Exam not found or access denied.');
            return;
        }

        // Get JSON input
        $input = file_get_contents('php://input');
        $examData = json_decode($input, true);
        
        if (!$examData) {
            $this->showError('Invalid exam data.');
            return;
        }

        $result = $this->examService->updateExam($examId, $examData);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function deleteExam($examId): void
    {
        $this->ensureFaculty();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $currentUser = $this->authService->getCurrentUserModel();
        
        // Verify ownership
        $exam = $this->examService->getExamById($examId);
        if (!$exam || $exam->getFacultyId() != $currentUser->getUserId()) {
            $this->showError('Exam not found or access denied.');
            return;
        }

        $result = $this->examService->deleteExam($examId);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function viewExam($examId): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        $exam = $this->examService->getExamById($examId);
        
        if (!$exam || $exam->getFacultyId() != $currentUser->getUserId()) {
            $this->showError('Exam not found or access denied.');
            return;
        }

        $questions = $this->examService->getExamQuestions($examId);
        
        // Load options for each question
        foreach ($questions as $question) {
            if ($question->getQuestionType() === 'multiple_choice') {
                $options = $this->examService->getQuestionOptions($question->getId());
                $question->setOptions($options);
            }
        }
        
        $this->view->display('faculty.view-exam', [
            'faculty' => $currentUser,
            'exam' => $exam,
            'questions' => $questions
        ]);
    }

    public function recalculateScore($attemptId): void
    {
        $this->ensureFaculty();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showError('Invalid request method.');
            return;
        }

        $currentUser = $this->authService->getCurrentUserModel();
        
        // Verify the attempt belongs to an exam created by this faculty
        $attempt = $this->examService->getExamAttemptDetails($attemptId);
        if (!$attempt) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Exam attempt not found']);
            return;
        }

        $result = $this->examService->recalculateExamScore($attemptId);
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    private function ensureFaculty(): void
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('faculty');
    }

    private function showError($message): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }
}
