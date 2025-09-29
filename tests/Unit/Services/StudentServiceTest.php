<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Services\Student\StudentService;
use App\DAO\Auth\UserDAO;
use App\DAO\Assignment\AssignmentDAO;
use App\Models\User;
use App\Models\SubjectAssignment;

class StudentServiceTest extends TestCase
{
    private StudentService $studentService;
    private MockObject $mockUserDAO;
    private MockObject $mockAssignmentDAO;

    protected function setUp(): void
    {
        $this->mockUserDAO = $this->createMock(UserDAO::class);
        $this->mockAssignmentDAO = $this->createMock(AssignmentDAO::class);
        
        $this->studentService = new StudentService(
            $this->mockUserDAO,
            $this->mockAssignmentDAO
        );
    }

    /** @test */
    public function it_should_get_students_for_assignment()
    {
        $assignmentId = 1;
        $mockAssignment = $this->createMockAssignment();
        $mockStudents = $this->createMockStudents();

        $this->mockAssignmentDAO
            ->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn($mockAssignment);

        $this->mockUserDAO
            ->expects($this->once())
            ->method('getStudentsByYearAndSection')
            ->with('2', 'A')
            ->willReturn($mockStudents);

        $result = $this->studentService->getStudentsForAssignment($assignmentId);

        $this->assertEquals($mockStudents, $result);
    }

    /** @test */
    public function it_should_return_empty_array_when_assignment_not_found()
    {
        $assignmentId = 999;

        $this->mockAssignmentDAO
            ->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn(null);

        $this->mockUserDAO
            ->expects($this->never())
            ->method('getStudentsByYearAndSection');

        $result = $this->studentService->getStudentsForAssignment($assignmentId);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_should_get_students_for_faculty()
    {
        $facultyId = 1;
        $mockAssignments = [$this->createMockAssignment()];
        $mockStudents = $this->createMockStudents();

        $this->mockAssignmentDAO
            ->expects($this->once())
            ->method('getByFilters')
            ->with(['faculty_id' => $facultyId])
            ->willReturn($mockAssignments);

        $this->mockUserDAO
            ->expects($this->once())
            ->method('getStudentsByYearAndSection')
            ->with('2', 'A')
            ->willReturn($mockStudents);

        $result = $this->studentService->getStudentsForFaculty($facultyId);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(User::class, $result);
    }

    /** @test */
    public function it_should_remove_duplicate_students_for_faculty()
    {
        $facultyId = 1;
        $mockAssignments = [
            $this->createMockAssignment(),
            $this->createMockAssignment() // Same assignment twice
        ];
        $mockStudents = $this->createMockStudents();

        $this->mockAssignmentDAO
            ->expects($this->once())
            ->method('getByFilters')
            ->with(['faculty_id' => $facultyId])
            ->willReturn($mockAssignments);

        $this->mockUserDAO
            ->expects($this->exactly(2))
            ->method('getStudentsByYearAndSection')
            ->with('2', 'A')
            ->willReturn($mockStudents);

        $result = $this->studentService->getStudentsForFaculty($facultyId);

        // Should only return unique students (2 students, not 4)
        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_should_get_students_for_subject()
    {
        $subjectId = 1;
        $facultyId = 1;
        $mockAssignments = [$this->createMockAssignment()];
        $mockStudents = $this->createMockStudents();

        $this->mockAssignmentDAO
            ->expects($this->once())
            ->method('getByFilters')
            ->with(['subject_id' => $subjectId, 'faculty_id' => $facultyId])
            ->willReturn($mockAssignments);

        $this->mockUserDAO
            ->expects($this->once())
            ->method('getStudentsByYearAndSection')
            ->with('2', 'A')
            ->willReturn($mockStudents);

        $result = $this->studentService->getStudentsForSubject($subjectId, $facultyId);

        $this->assertEquals($mockStudents, $result);
    }

    /** @test */
    public function it_should_return_empty_array_when_no_assignments_for_subject()
    {
        $subjectId = 999;
        $facultyId = 1;

        $this->mockAssignmentDAO
            ->expects($this->once())
            ->method('getByFilters')
            ->with(['subject_id' => $subjectId, 'faculty_id' => $facultyId])
            ->willReturn([]);

        $this->mockUserDAO
            ->expects($this->never())
            ->method('getStudentsByYearAndSection');

        $result = $this->studentService->getStudentsForSubject($subjectId, $facultyId);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_should_get_student_stats()
    {
        $facultyId = 1;
        $mockStudents = [
            new User(['user_id' => 1, 'year_level' => '2', 'section' => 'A']),
            new User(['user_id' => 2, 'year_level' => '2', 'section' => 'A']),
            new User(['user_id' => 3, 'year_level' => '3', 'section' => 'B'])
        ];

        // Mock the getStudentsForFaculty call
        $this->mockAssignmentDAO
            ->method('getByFilters')
            ->willReturn([$this->createMockAssignment()]);

        $this->mockUserDAO
            ->method('getStudentsByYearAndSection')
            ->willReturn($mockStudents);

        $result = $this->studentService->getStudentStats($facultyId);

        $this->assertEquals(3, $result['total_students']);
        $this->assertEquals(['2' => 2, '3' => 1], $result['by_year_level']);
        $this->assertEquals(['A' => 2, 'B' => 1], $result['by_section']);
    }

    /** @test */
    public function it_should_return_empty_stats_when_no_students()
    {
        $facultyId = 1;

        $this->mockAssignmentDAO
            ->method('getByFilters')
            ->willReturn([]);

        $result = $this->studentService->getStudentStats($facultyId);

        $this->assertEquals(0, $result['total_students']);
        $this->assertEquals([], $result['by_year_level']);
        $this->assertEquals([], $result['by_section']);
    }

    private function createMockAssignment(): SubjectAssignment
    {
        $assignment = $this->createMock(SubjectAssignment::class);
        $assignment->method('getYearLevel')->willReturn('2');
        $assignment->method('getSection')->willReturn('A');
        $assignment->method('toArray')->willReturn([
            'subject_code' => 'CS101',
            'subject_name' => 'Computer Science'
        ]);
        $assignment->method('getSemester')->willReturn('1st');
        $assignment->method('getAcademicYear')->willReturn('2024-2025');
        
        return $assignment;
    }

    private function createMockStudents(): array
    {
        return [
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
    }
}
