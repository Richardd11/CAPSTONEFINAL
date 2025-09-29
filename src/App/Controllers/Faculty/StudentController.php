<?php

namespace App\Controllers\Faculty;

use App\Services\Auth\AuthService;
use App\Services\Student\StudentService;
use App\Core\View;

class StudentController
{
    private AuthService $authService;
    private StudentService $studentService;
    private View $view;

    public function __construct(
        AuthService $authService = null,
        StudentService $studentService = null,
        View $view = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->studentService = $studentService ?? new StudentService();
        $this->view = $view ?? new View();
    }

    public function listStudents(): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        $students = $this->studentService->getStudentsForFaculty($currentUser->getUserId());
        $stats = $this->studentService->getStudentStats($currentUser->getUserId());
        
        $this->view->display('faculty.students', [
            'faculty' => $currentUser,
            'students' => $students,
            'stats' => $stats
        ]);
    }

    public function getStudentsForSubject($subjectId): void
    {
        $this->ensureFaculty();
        $currentUser = $this->authService->getCurrentUserModel();
        
        $students = $this->studentService->getStudentsForSubject($subjectId, $currentUser->getUserId());
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'students' => array_map(function($student) {
                return [
                    'user_id' => $student->getUserId(),
                    'school_id' => $student->getSchoolId(),
                    'full_name' => $student->getFullName(),
                    'year_level' => $student->getYearLevel(),
                    'section' => $student->getSection()
                ];
            }, $students)
        ]);
    }

    private function ensureFaculty(): void
    {
        $this->authService->requireAuth();
        $this->authService->requireRole('faculty');
    }
}
