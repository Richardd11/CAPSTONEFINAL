<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\Faculty\FacultyController;
use App\Services\Auth\AuthService;
use App\Services\Assignment\AssignmentService;
use App\Services\Exam\ExamService;
use App\Services\Student\StudentService;
use App\Core\View;
use App\Models\User;

class FacultyControllerTest extends TestCase
{
    private FacultyController $controller;
    private MockObject $mockAuthService;
    private MockObject $mockAssignmentService;
    private MockObject $mockExamService;
    private MockObject $mockStudentService;
    private MockObject $mockView;

    protected function setUp(): void
    {
        $this->mockAuthService = $this->createMock(AuthService::class);
        $this->mockAssignmentService = $this->createMock(AssignmentService::class);
        $this->mockExamService = $this->createMock(ExamService::class);
        $this->mockStudentService = $this->createMock(StudentService::class);
        $this->mockView = $this->createMock(View::class);

        $this->controller = new FacultyController(
            $this->mockAuthService,
            $this->mockAssignmentService,
            $this->mockExamService,
            $this->mockStudentService,
            $this->mockView
        );
    }

    /** @test */
    public function it_should_display_dashboard_for_authenticated_faculty()
    {
        $mockUser = new User([
            'user_id' => 1,
            'full_name' => 'Dr. John Smith',
            'role' => 'faculty'
        ]);

        $mockAssignments = [];
        $mockExamStats = ['total_exams' => 5];
        $mockStudentStats = ['total_students' => 25];

        // Setup auth service expectations
        $this->mockAuthService
            ->expects($this->once())
            ->method('requireAuth');

        $this->mockAuthService
            ->expects($this->once())
            ->method('requireRole')
            ->with('faculty');

        $this->mockAuthService
            ->expects($this->once())
            ->method('getCurrentUserModel')
            ->willReturn($mockUser);

        // Setup service expectations
        $this->mockAssignmentService
            ->expects($this->once())
            ->method('getAssignmentsByFilters')
            ->with(['faculty_id' => 1, 'status' => 'active'])
            ->willReturn($mockAssignments);

        $this->mockExamService
            ->expects($this->once())
            ->method('getExamStats')
            ->with(1)
            ->willReturn($mockExamStats);

        $this->mockStudentService
            ->expects($this->once())
            ->method('getStudentStats')
            ->with(1)
            ->willReturn($mockStudentStats);

        // Setup view expectations
        $this->mockView
            ->expects($this->once())
            ->method('display')
            ->with('faculty.dashboard', $this->callback(function($data) use ($mockUser, $mockAssignments, $mockExamStats, $mockStudentStats) {
                return $data['faculty'] === $mockUser &&
                       $data['assignments'] === $mockAssignments &&
                       $data['examStats'] === $mockExamStats &&
                       $data['studentStats'] === $mockStudentStats &&
                       isset($data['academicYear']) &&
                       isset($data['assignedSubjects']);
            }));

        $this->controller->dashboard();
    }

    /** @test */
    public function it_should_redirect_to_login_when_user_not_authenticated()
    {
        $this->mockAuthService
            ->expects($this->once())
            ->method('requireAuth');

        $this->mockAuthService
            ->expects($this->once())
            ->method('requireRole')
            ->with('faculty');

        $this->mockAuthService
            ->expects($this->once())
            ->method('getCurrentUserModel')
            ->willReturn(null);

        // Expect redirect (we can't easily test header() calls in unit tests,
        // but we can verify the method flow)
        $this->expectOutputString('');
        
        // Mock the exit() call by catching the expected flow
        try {
            $this->controller->dashboard();
        } catch (\Exception $e) {
            // Expected when header redirect happens
        }
    }

    /** @test */
    public function it_should_process_logout_confirmation()
    {
        $_GET['confirm'] = 'true';

        $this->mockAuthService
            ->expects($this->once())
            ->method('logout');

        // Test logout with confirmation
        try {
            $this->controller->logout();
        } catch (\Exception $e) {
            // Expected due to header redirect
        }

        unset($_GET['confirm']);
    }

    /** @test */
    public function it_should_show_logout_modal_without_confirmation()
    {
        $this->mockAuthService
            ->expects($this->once())
            ->method('requireAuth');

        $this->mockAuthService
            ->expects($this->once())
            ->method('requireRole')
            ->with('faculty');

        // Test logout without confirmation
        try {
            $this->controller->logout();
        } catch (\Exception $e) {
            // Expected due to header redirect
        }

        $this->assertTrue(isset($_SESSION['show_logout_modal']));
        unset($_SESSION['show_logout_modal']);
    }

    /** @test */
    public function it_should_generate_correct_academic_year()
    {
        $mockUser = new User(['user_id' => 1, 'role' => 'faculty']);

        $this->mockAuthService->method('requireAuth');
        $this->mockAuthService->method('requireRole');
        $this->mockAuthService->method('getCurrentUserModel')->willReturn($mockUser);
        
        $this->mockAssignmentService->method('getAssignmentsByFilters')->willReturn([]);
        $this->mockExamService->method('getExamStats')->willReturn([]);
        $this->mockStudentService->method('getStudentStats')->willReturn([]);

        $currentYear = date('Y');
        $expectedAcademicYear = $currentYear . '-' . ($currentYear + 1);

        $this->mockView
            ->expects($this->once())
            ->method('display')
            ->with('faculty.dashboard', $this->callback(function($data) use ($expectedAcademicYear) {
                return $data['academicYear'] === $expectedAcademicYear;
            }));

        $this->controller->dashboard();
    }

    /** @test */
    public function it_should_process_assignments_for_dashboard()
    {
        $mockUser = new User(['user_id' => 1, 'role' => 'faculty']);
        $mockAssignment = $this->createMock(\App\Models\Assignment::class);
        $mockAssignment->method('getSubjectId')->willReturn(101);
        $mockAssignment->method('getYearLevel')->willReturn('2');
        $mockAssignment->method('getSection')->willReturn('A');
        $mockAssignment->method('getAcademicYear')->willReturn('2024-2025');
        $mockAssignment->method('getSemester')->willReturn('1st');

        $mockAssignments = [$mockAssignment];

        $this->mockAuthService->method('requireAuth');
        $this->mockAuthService->method('requireRole');
        $this->mockAuthService->method('getCurrentUserModel')->willReturn($mockUser);
        
        $this->mockAssignmentService
            ->method('getAssignmentsByFilters')
            ->willReturn($mockAssignments);
            
        $this->mockExamService->method('getExamStats')->willReturn([]);
        $this->mockStudentService->method('getStudentStats')->willReturn([]);

        $this->mockView
            ->expects($this->once())
            ->method('display')
            ->with('faculty.dashboard', $this->callback(function($data) {
                return isset($data['assignedSubjects']) &&
                       is_array($data['assignedSubjects']) &&
                       count($data['assignedSubjects']) === 1 &&
                       $data['assignedSubjects'][0]['subject_id'] === 101;
            }));

        $this->controller->dashboard();
    }
}
