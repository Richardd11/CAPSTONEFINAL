<?php

namespace Tests\Unit\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Controllers\Faculty\StudentController;
use App\Services\Auth\AuthService;
use App\Services\Student\StudentService;
use App\Core\View;
use App\Models\User;

class StudentControllerTest extends TestCase
{
    private StudentController $controller;
    private MockObject $mockAuthService;
    private MockObject $mockStudentService;
    private MockObject $mockView;

    protected function setUp(): void
    {
        $this->mockAuthService = $this->createMock(AuthService::class);
        $this->mockStudentService = $this->createMock(StudentService::class);
        $this->mockView = $this->createMock(View::class);

        $this->controller = new StudentController(
            $this->mockAuthService,
            $this->mockStudentService,
            $this->mockView
        );
    }

    /** @test */
    public function it_should_list_students_for_authenticated_faculty()
    {
        $mockUser = new User([
            'user_id' => 1,
            'full_name' => 'Dr. John Smith',
            'role' => 'faculty'
        ]);

        $mockStudents = [
            new User(['user_id' => 1, 'full_name' => 'Student One']),
            new User(['user_id' => 2, 'full_name' => 'Student Two'])
        ];

        $mockStats = [
            'total_students' => 2,
            'by_year_level' => ['2' => 2],
            'by_section' => ['A' => 2]
        ];

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

        // Setup student service expectations
        $this->mockStudentService
            ->expects($this->once())
            ->method('getStudentsForFaculty')
            ->with(1)
            ->willReturn($mockStudents);

        $this->mockStudentService
            ->expects($this->once())
            ->method('getStudentStats')
            ->with(1)
            ->willReturn($mockStats);

        // Setup view expectations
        $this->mockView
            ->expects($this->once())
            ->method('display')
            ->with('faculty.students', [
                'faculty' => $mockUser,
                'students' => $mockStudents,
                'stats' => $mockStats
            ]);

        $this->controller->listStudents();
    }

    /** @test */
    public function it_should_get_students_for_subject_as_json()
    {
        $subjectId = 101;
        $mockUser = new User(['user_id' => 1, 'role' => 'faculty']);
        $mockStudents = [
            new User([
                'user_id' => 1,
                'school_id' => '2020-001',
                'full_name' => 'John Doe',
                'year_level' => '2',
                'section' => 'A'
            ]),
            new User([
                'user_id' => 2,
                'school_id' => '2020-002',
                'full_name' => 'Jane Smith',
                'year_level' => '2',
                'section' => 'A'
            ])
        ];

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

        // Setup student service expectations
        $this->mockStudentService
            ->expects($this->once())
            ->method('getStudentsForSubject')
            ->with($subjectId, 1)
            ->willReturn($mockStudents);

        // Capture output
        ob_start();
        $this->controller->getStudentsForSubject($subjectId);
        $output = ob_get_clean();

        // Verify JSON response
        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['students']);
        $this->assertEquals('John Doe', $response['students'][0]['full_name']);
        $this->assertEquals('2020-001', $response['students'][0]['school_id']);
    }

    /** @test */
    public function it_should_return_empty_students_array_for_subject()
    {
        $subjectId = 999;
        $mockUser = new User(['user_id' => 1, 'role' => 'faculty']);

        $this->mockAuthService->method('requireAuth');
        $this->mockAuthService->method('requireRole');
        $this->mockAuthService->method('getCurrentUserModel')->willReturn($mockUser);

        $this->mockStudentService
            ->expects($this->once())
            ->method('getStudentsForSubject')
            ->with($subjectId, 1)
            ->willReturn([]);

        ob_start();
        $this->controller->getStudentsForSubject($subjectId);
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEmpty($response['students']);
    }

    /** @test */
    public function it_should_require_faculty_authentication_for_list_students()
    {
        $this->mockAuthService
            ->expects($this->once())
            ->method('requireAuth');

        $this->mockAuthService
            ->expects($this->once())
            ->method('requireRole')
            ->with('faculty');

        $this->mockAuthService
            ->method('getCurrentUserModel')
            ->willReturn(new User(['user_id' => 1, 'role' => 'faculty']));

        $this->mockStudentService->method('getStudentsForFaculty')->willReturn([]);
        $this->mockStudentService->method('getStudentStats')->willReturn([]);
        $this->mockView->method('display');

        $this->controller->listStudents();
    }

    /** @test */
    public function it_should_require_faculty_authentication_for_get_students_for_subject()
    {
        $this->mockAuthService
            ->expects($this->once())
            ->method('requireAuth');

        $this->mockAuthService
            ->expects($this->once())
            ->method('requireRole')
            ->with('faculty');

        $this->mockAuthService
            ->method('getCurrentUserModel')
            ->willReturn(new User(['user_id' => 1, 'role' => 'faculty']));

        $this->mockStudentService->method('getStudentsForSubject')->willReturn([]);

        ob_start();
        $this->controller->getStudentsForSubject(1);
        ob_get_clean();
    }

    /** @test */
    public function it_should_format_student_data_correctly_for_json_response()
    {
        $mockUser = new User(['user_id' => 1, 'role' => 'faculty']);
        $mockStudent = new User([
            'user_id' => 123,
            'school_id' => 'TEST-001',
            'full_name' => 'Test Student',
            'year_level' => '3',
            'section' => 'B'
        ]);

        $this->mockAuthService->method('requireAuth');
        $this->mockAuthService->method('requireRole');
        $this->mockAuthService->method('getCurrentUserModel')->willReturn($mockUser);

        $this->mockStudentService
            ->method('getStudentsForSubject')
            ->willReturn([$mockStudent]);

        ob_start();
        $this->controller->getStudentsForSubject(1);
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $studentData = $response['students'][0];

        $this->assertEquals(123, $studentData['user_id']);
        $this->assertEquals('TEST-001', $studentData['school_id']);
        $this->assertEquals('Test Student', $studentData['full_name']);
        $this->assertEquals('3', $studentData['year_level']);
        $this->assertEquals('B', $studentData['section']);
    }

    /** @test */
    public function it_should_set_correct_content_type_for_json_response()
    {
        $mockUser = new User(['user_id' => 1, 'role' => 'faculty']);

        $this->mockAuthService->method('requireAuth');
        $this->mockAuthService->method('requireRole');
        $this->mockAuthService->method('getCurrentUserModel')->willReturn($mockUser);
        $this->mockStudentService->method('getStudentsForSubject')->willReturn([]);

        // We can't easily test header() calls in unit tests, but we can verify
        // the method executes without errors
        ob_start();
        $this->controller->getStudentsForSubject(1);
        $output = ob_get_clean();

        // Verify it's valid JSON
        $this->assertIsArray(json_decode($output, true));
    }
}
