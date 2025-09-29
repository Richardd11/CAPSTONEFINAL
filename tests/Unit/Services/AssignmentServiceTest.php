<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Services\Assignment\AssignmentService;
use App\Interfaces\AssignmentDAOInterface;
use App\Interfaces\SubjectDAOInterface;
use App\Interfaces\UserDAOInterface;
use App\Models\SubjectAssignment;
use App\Models\Subject;
use App\Models\User;

class AssignmentServiceTest extends TestCase
{
    /** @var MockObject&AssignmentDAOInterface */
    private $mockAssignmentDAO;
    
    /** @var MockObject&SubjectDAOInterface */
    private $mockSubjectDAO;
    
    /** @var MockObject&UserDAOInterface */
    private $mockUserDAO;
    
    private AssignmentService $assignmentService;

    protected function setUp(): void
    {
        $this->mockAssignmentDAO = $this->createMock(AssignmentDAOInterface::class);
        $this->mockSubjectDAO = $this->createMock(SubjectDAOInterface::class);
        $this->mockUserDAO = $this->createMock(UserDAOInterface::class);
        
        $this->assignmentService = new AssignmentService(
            $this->mockAssignmentDAO,
            $this->mockSubjectDAO,
            $this->mockUserDAO
        );
    }

    /**
     * @test
     */
    public function it_should_get_all_assignments()
    {
        $expectedAssignments = [
            new SubjectAssignment(['id' => 1]),
            new SubjectAssignment(['id' => 2])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedAssignments);

        $result = $this->assignmentService->getAllAssignments();

        $this->assertCount(2, $result);
        $this->assertEquals($expectedAssignments, $result);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_by_id()
    {
        $expectedAssignment = new SubjectAssignment(['id' => 1]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($expectedAssignment);

        $result = $this->assignmentService->getAssignmentById(1);

        $this->assertEquals($expectedAssignment, $result);
    }

    /**
     * @test
     */
    public function it_should_create_assignment_successfully()
    {
        $assignmentData = [
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $mockSubject = new Subject(['subject_id' => 101]);
        $mockFaculty = new User(['user_id' => 201, 'role' => 'faculty']);
        $createdAssignment = new SubjectAssignment(array_merge($assignmentData, ['id' => 1]));

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(101)
            ->willReturn($mockSubject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(201)
            ->willReturn($mockFaculty);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('create')
            ->willReturn($createdAssignment);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment created successfully.', $result['message']);
        $this->assertEquals($createdAssignment, $result['data']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_with_invalid_data()
    {
        $assignmentData = [
            // Missing required fields
            'subject_id' => null,
            'faculty_id' => null
        ];

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Validation failed', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_duplicate_assignment()
    {
        $assignmentData = [
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(true);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Assignment already exists', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_with_invalid_subject()
    {
        $assignmentData = [
            'subject_id' => 999,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(999)
            ->willReturn(null);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Subject not found.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_create_assignment_with_invalid_faculty()
    {
        $assignmentData = [
            'subject_id' => 101,
            'faculty_id' => 999,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ];

        $mockSubject = new Subject(['subject_id' => 101]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(101)
            ->willReturn($mockSubject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->assignmentService->createAssignment($assignmentData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Faculty not found or invalid faculty member.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_update_assignment_successfully()
    {
        $assignmentId = 1;
        $updateData = [
            'year_level' => '2nd Year',
            'section' => 'B'
        ];

        $existingAssignment = new SubjectAssignment([
            'id' => 1,
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active'
        ]);

        $mockSubject = new Subject(['subject_id' => 101]);
        $mockFaculty = new User(['user_id' => 201, 'role' => 'faculty']);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn($existingAssignment);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('assignmentExists')
            ->willReturn(false);

        $this->mockSubjectDAO->expects($this->once())
            ->method('getById')
            ->with(101)
            ->willReturn($mockSubject);

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(201)
            ->willReturn($mockFaculty);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->assignmentService->updateAssignment($assignmentId, $updateData);

        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment updated successfully.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_update_non_existent_assignment()
    {
        $assignmentId = 999;
        $updateData = ['year_level' => '2nd Year'];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn(null);

        $result = $this->assignmentService->updateAssignment($assignmentId, $updateData);

        $this->assertFalse($result['success']);
        $this->assertEquals('Assignment not found.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_delete_assignment_successfully()
    {
        $assignmentId = 1;
        $existingAssignment = new SubjectAssignment(['id' => 1]);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn($existingAssignment);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('delete')
            ->with($assignmentId)
            ->willReturn(true);

        $result = $this->assignmentService->deleteAssignment($assignmentId);

        $this->assertTrue($result['success']);
        $this->assertEquals('Assignment deleted successfully.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_fail_to_delete_non_existent_assignment()
    {
        $assignmentId = 999;

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getById')
            ->with($assignmentId)
            ->willReturn(null);

        $result = $this->assignmentService->deleteAssignment($assignmentId);

        $this->assertFalse($result['success']);
        $this->assertEquals('Assignment not found.', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_get_assignments_by_filters()
    {
        $filters = ['faculty_id' => 201, 'academic_year' => '2024-2025'];
        $expectedAssignments = [
            new SubjectAssignment(['id' => 1]),
            new SubjectAssignment(['id' => 2])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getByFilters')
            ->with($filters)
            ->willReturn($expectedAssignments);

        $result = $this->assignmentService->getAssignmentsByFilters($filters);

        $this->assertCount(2, $result);
        $this->assertEquals($expectedAssignments, $result);
    }

    /**
     * @test
     */
    public function it_should_get_faculty_workload()
    {
        $facultyId = 201;
        $academicYear = '2024-2025';
        $expectedWorkload = [
            new SubjectAssignment(['id' => 1]),
            new SubjectAssignment(['id' => 2])
        ];

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getFacultyWorkload')
            ->with($facultyId, $academicYear)
            ->willReturn($expectedWorkload);

        $result = $this->assignmentService->getFacultyWorkload($facultyId, $academicYear);

        $this->assertCount(2, $result);
        $this->assertEquals($expectedWorkload, $result);
    }

    /**
     * @test
     */
    public function it_should_check_schedule_conflicts()
    {
        $assignmentData = [
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ];

        $mockFaculty = new User(['user_id' => 201, 'role' => 'faculty']);
        $existingAssignments = [];

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(201)
            ->willReturn($mockFaculty);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getFacultyAssignments')
            ->with(201, '2024-2025')
            ->willReturn($existingAssignments);

        $result = $this->assignmentService->checkScheduleConflicts($assignmentData);

        $this->assertFalse($result['hasConflict']);
        $this->assertEquals('No conflicts found', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_detect_schedule_conflicts()
    {
        $assignmentData = [
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ];

        $mockFaculty = new User(['user_id' => 201, 'role' => 'faculty']);
        
        // Create a mock assignment that conflicts
        $conflictingAssignment = $this->createMock(SubjectAssignment::class);
        $conflictingAssignment->method('getId')->willReturn(1);
        $conflictingAssignment->method('getYearLevel')->willReturn('1st Year');
        $conflictingAssignment->method('getSection')->willReturn('A');
        $conflictingAssignment->method('getSemester')->willReturn('1st Semester');

        $this->mockUserDAO->expects($this->once())
            ->method('findById')
            ->with(201)
            ->willReturn($mockFaculty);

        $this->mockAssignmentDAO->expects($this->once())
            ->method('getFacultyAssignments')
            ->with(201, '2024-2025')
            ->willReturn([$conflictingAssignment]);

        $result = $this->assignmentService->checkScheduleConflicts($assignmentData);

        $this->assertTrue($result['hasConflict']);
        $this->assertStringContainsString('Faculty already assigned', $result['message']);
    }

    /**
     * @test
     */
    public function it_should_get_all_faculty()
    {
        $expectedFaculty = [
            ['user_id' => 201, 'full_name' => 'Dr. Smith'],
            ['user_id' => 202, 'full_name' => 'Dr. Jones']
        ];

        $this->mockUserDAO->expects($this->once())
            ->method('getUsersByRole')
            ->with('faculty')
            ->willReturn($expectedFaculty);

        $result = $this->assignmentService->getAllFaculty();

        $this->assertCount(2, $result);
        $this->assertEquals($expectedFaculty, $result);
    }

    /**
     * @test
     */
    public function it_should_get_all_subjects()
    {
        $expectedSubjects = [
            new Subject(['subject_id' => 101]),
            new Subject(['subject_id' => 102])
        ];

        $this->mockSubjectDAO->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedSubjects);

        $result = $this->assignmentService->getAllSubjects();

        $this->assertCount(2, $result);
        $this->assertEquals($expectedSubjects, $result);
    }

    /**
     * @test
     */
    public function it_should_get_year_levels()
    {
        $result = $this->assignmentService->getYearLevels();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('1st Year', $result);
        $this->assertArrayHasKey('2nd Year', $result);
        $this->assertArrayHasKey('3rd Year', $result);
        $this->assertArrayHasKey('4th Year', $result);
    }

    /**
     * @test
     */
    public function it_should_get_sections()
    {
        $result = $this->assignmentService->getSections();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('A', $result);
        $this->assertArrayHasKey('B', $result);
        $this->assertArrayHasKey('C', $result);
    }

    /**
     * @test
     */
    public function it_should_get_academic_years()
    {
        $result = $this->assignmentService->getAcademicYears();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        // Check that it generates years in the correct format
        foreach ($result as $key => $value) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{4}$/', $key);
        }
    }

    /**
     * @test
     */
    public function it_should_get_semesters()
    {
        $result = $this->assignmentService->getSemesters();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('1st Semester', $result);
        $this->assertArrayHasKey('2nd Semester', $result);
        $this->assertArrayHasKey('Summer', $result);
    }

    /**
     * @test
     */
    public function it_should_convert_assignments_to_array()
    {
        $assignments = [
            new SubjectAssignment(['id' => 1, 'subject_id' => 101]),
            new SubjectAssignment(['id' => 2, 'subject_id' => 102])
        ];

        $result = $this->assignmentService->assignmentsToArray($assignments);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertIsArray($result[0]);
        $this->assertIsArray($result[1]);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals(2, $result[1]['id']);
    }
}
