<?php

namespace Tests\Unit\DAO;

use PHPUnit\Framework\TestCase;
use App\DAO\Assignment\AssignmentDAO;
use App\Models\SubjectAssignment;
use App\Config\Database;
use PDO;
use PDOStatement;

class AssignmentDAOTest extends TestCase
{
    private $mockDb;
    private $mockStmt;
    private $assignmentDAO;

    protected function setUp(): void
    {
        // Create mock PDO and PDOStatement
        $this->mockDb = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // Create a partial mock of Database to inject our mock PDO
        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->mockDb);
        
        // Use reflection to set the singleton instance
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, $mockDatabase);
        
        $this->assignmentDAO = new AssignmentDAO();
    }

    protected function tearDown(): void
    {
        // Reset the Database singleton
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    /**
     * @test
     */
    public function it_should_get_all_assignments()
    {
        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 101,
                'faculty_id' => 201,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test',
                'subject_code' => 'CS101',
                'subject_name' => 'Computer Science',
                'faculty_name' => 'Dr. Smith'
            ]
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getAll();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
        $this->assertEquals(1, $result[0]->getId());
    }

    /**
     * @test
     */
    public function it_should_get_assignment_by_id()
    {
        $expectedData = [
            'id' => 1,
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test',
            'subject_code' => 'CS101',
            'subject_name' => 'Computer Science',
            'faculty_name' => 'Dr. Smith'
        ];

        $this->mockStmt->method('execute')->with([1])->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn($expectedData);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getById(1);

        $this->assertInstanceOf(SubjectAssignment::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals(101, $result->getSubjectId());
    }

    /**
     * @test
     */
    public function it_should_return_null_when_assignment_not_found()
    {
        $this->mockStmt->method('execute')->with([999])->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(false);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getById(999);

        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_should_create_new_assignment()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'New assignment'
        ]);

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);
        $this->mockDb->method('lastInsertId')->willReturn('5');

        $result = $this->assignmentDAO->create($assignment);

        $this->assertInstanceOf(SubjectAssignment::class, $result);
        $this->assertEquals(5, $result->getId());
    }

    /**
     * @test
     */
    public function it_should_update_existing_assignment()
    {
        $assignment = new SubjectAssignment([
            'id' => 1,
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '2nd Year',
            'section' => 'B',
            'academic_year' => '2024-2025',
            'semester' => '2nd Semester',
            'status' => 'active',
            'notes' => 'Updated assignment'
        ]);

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->update($assignment);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_delete_assignment()
    {
        $this->mockStmt->method('execute')->with([1])->willReturn(true);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->delete(1);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_assignment_exists()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(1);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->assignmentExists(
            101, '1st Year', 'A', '2024-2025', '1st Semester'
        );

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_check_if_assignment_exists_excluding_id()
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(0);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->assignmentExists(
            101, '1st Year', 'A', '2024-2025', '1st Semester', 1
        );

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_get_assignments_by_filters()
    {
        $filters = [
            'faculty_id' => 201,
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ];

        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 101,
                'faculty_id' => 201,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test',
                'subject_code' => 'CS101',
                'subject_name' => 'Computer Science',
                'faculty_name' => 'Dr. Smith'
            ]
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getByFilters($filters);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /**
     * @test
     */
    public function it_should_get_faculty_workload()
    {
        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 101,
                'faculty_id' => 201,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test',
                'subject_code' => 'CS101',
                'subject_name' => 'Computer Science',
                'units' => 3
            ]
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')
            ->willReturnOnConsecutiveCalls($expectedData[0], false);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getFacultyWorkload(201, '2024-2025');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
    }

    /**
     * @test
     */
    public function it_should_get_faculty_assignments()
    {
        $expectedData = [
            [
                'id' => 1,
                'subject_id' => 101,
                'faculty_id' => 201,
                'year_level' => '1st Year',
                'section' => 'A',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Test',
                'subject_code' => 'CS101',
                'subject_name' => 'Computer Science',
                'faculty_name' => 'Dr. Smith'
            ],
            [
                'id' => 2,
                'subject_id' => 102,
                'faculty_id' => 201,
                'year_level' => '2nd Year',
                'section' => 'B',
                'academic_year' => '2024-2025',
                'semester' => '1st Semester',
                'status' => 'active',
                'notes' => 'Another test',
                'subject_code' => 'MATH101',
                'subject_name' => 'Mathematics',
                'faculty_name' => 'Dr. Smith'
            ]
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')
            ->willReturnOnConsecutiveCalls($expectedData[0], $expectedData[1], false);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getFacultyAssignments(201, '2024-2025');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(SubjectAssignment::class, $result[0]);
        $this->assertInstanceOf(SubjectAssignment::class, $result[1]);
        $this->assertEquals(201, $result[0]->getFacultyId());
        $this->assertEquals(201, $result[1]->getFacultyId());
    }

    /**
     * @test
     */
    public function it_should_get_unassigned_subjects()
    {
        $expectedData = [
            [
                'subject_id' => 103,
                'subject_code' => 'PHY101',
                'subject_name' => 'Physics',
                'description' => 'Basic Physics',
                'units' => 3,
                'year_level' => '1st Year',
                'semester' => '1st Semester'
            ]
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn($expectedData);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getUnassignedSubjects('2024-2025', '1st Semester');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('PHY101', $result[0]['subject_code']);
    }

    /**
     * @test
     */
    public function it_should_get_assignment_statistics()
    {
        $expectedData = [
            'total_assignments' => 10,
            'active_assignments' => 7,
            'inactive_assignments' => 2,
            'pending_assignments' => 1,
            'total_faculty' => 5,
            'total_subjects' => 8
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn($expectedData);

        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->getAssignmentStats('2024-2025');

        $this->assertIsArray($result);
        $this->assertEquals(10, $result['total_assignments']);
        $this->assertEquals(7, $result['active_assignments']);
        $this->assertEquals(5, $result['total_faculty']);
    }

    /**
     * @test
     */
    public function it_should_handle_database_exceptions_in_create()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $this->mockDb->method('prepare')
            ->willThrowException(new \PDOException('Database error'));

        $this->expectException(\PDOException::class);
        $this->assignmentDAO->create($assignment);
    }

    /**
     * @test
     */
    public function it_should_return_null_when_create_fails()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $this->mockStmt->method('execute')->willReturn(false);
        $this->mockDb->method('prepare')->willReturn($this->mockStmt);

        $result = $this->assignmentDAO->create($assignment);

        $this->assertNull($result);
    }
}
