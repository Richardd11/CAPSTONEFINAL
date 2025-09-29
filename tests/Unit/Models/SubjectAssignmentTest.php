<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\SubjectAssignment;

class SubjectAssignmentTest extends TestCase
{
    private SubjectAssignment $assignment;

    protected function setUp(): void
    {
        $this->assignment = new SubjectAssignment([
            'id' => 1,
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'active',
            'notes' => 'Test assignment',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ]);
    }

    /**
     * @test
     */
    public function it_should_create_assignment_with_valid_data()
    {
        $this->assertEquals(1, $this->assignment->getId());
        $this->assertEquals(101, $this->assignment->getSubjectId());
        $this->assertEquals(201, $this->assignment->getFacultyId());
        $this->assertEquals('1st Year', $this->assignment->getYearLevel());
        $this->assertEquals('A', $this->assignment->getSection());
        $this->assertEquals('2024-2025', $this->assignment->getAcademicYear());
        $this->assertEquals('1st Semester', $this->assignment->getSemester());
        $this->assertEquals('active', $this->assignment->getStatus());
        $this->assertEquals('Test assignment', $this->assignment->getNotes());
    }

    /**
     * @test
     */
    public function it_should_create_assignment_with_minimal_data()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 102,
            'faculty_id' => 202,
            'year_level' => '2nd Year',
            'section' => 'B',
            'academic_year' => '2024-2025',
            'semester' => '2nd Semester'
        ]);

        $this->assertNull($assignment->getId());
        $this->assertEquals(102, $assignment->getSubjectId());
        $this->assertEquals(202, $assignment->getFacultyId());
        $this->assertEquals('2nd Year', $assignment->getYearLevel());
        $this->assertEquals('B', $assignment->getSection());
        $this->assertEquals('2024-2025', $assignment->getAcademicYear());
        $this->assertEquals('2nd Semester', $assignment->getSemester());
        $this->assertEquals('active', $assignment->getStatus()); // default value
        $this->assertEquals('', $assignment->getNotes()); // default empty
    }

    /**
     * @test
     */
    public function it_should_validate_required_fields()
    {
        $assignment = new SubjectAssignment([]);
        $errors = $assignment->validate();

        $this->assertContains('Subject ID is required', $errors);
        $this->assertContains('Faculty ID is required', $errors);
        $this->assertContains('Year level is required', $errors);
        $this->assertContains('Section is required', $errors);
        $this->assertContains('Academic year is required', $errors);
        $this->assertContains('Semester is required', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_year_level_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => 'Invalid Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Invalid year level. Must be one of: 1st Year, 2nd Year, 3rd Year, 4th Year', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_section_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'Z',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Invalid section. Must be one of: A, B, C, D, E, F', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_academic_year_format()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024',
            'semester' => '1st Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Invalid academic year format. Must be YYYY-YYYY', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_semester_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => 'Invalid Semester'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Invalid semester. Must be one of: 1st Semester, 2nd Semester, Summer', $errors);
    }

    /**
     * @test
     */
    public function it_should_validate_status_values()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'invalid'
        ]);

        $errors = $assignment->validate();
        $this->assertContains('Invalid status. Must be one of: active, inactive, completed, cancelled', $errors);
    }

    /**
     * @test
     */
    public function it_should_pass_validation_with_valid_data()
    {
        $errors = $this->assignment->validate();
        $this->assertEmpty($errors);
    }

    /**
     * @test
     */
    public function it_should_convert_to_array()
    {
        $array = $this->assignment->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(1, $array['id']);
        $this->assertEquals(101, $array['subject_id']);
        $this->assertEquals(201, $array['faculty_id']);
        $this->assertEquals('1st Year', $array['year_level']);
        $this->assertEquals('A', $array['section']);
        $this->assertEquals('2024-2025', $array['academic_year']);
        $this->assertEquals('1st Semester', $array['semester']);
        $this->assertEquals('active', $array['status']);
        $this->assertEquals('Test assignment', $array['notes']);
    }

    /**
     * @test
     */
    public function it_should_check_if_assignment_is_active()
    {
        $this->assertTrue($this->assignment->isActive());

        $inactiveAssignment = new SubjectAssignment([
            'subject_id' => 101,
            'faculty_id' => 201,
            'year_level' => '1st Year',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'semester' => '1st Semester',
            'status' => 'inactive'
        ]);

        $this->assertFalse($inactiveAssignment->isActive());
    }

    /**
     * @test
     */
    public function it_should_generate_assignment_key_for_uniqueness()
    {
        $key = $this->assignment->getAssignmentKey();
        $expectedKey = '101_1st Year_A_2024-2025_1st Semester';
        $this->assertEquals($expectedKey, $key);
    }

    /**
     * @test
     */
    public function it_should_set_and_get_properties()
    {
        $this->assignment->setId(2);
        $this->assertEquals(2, $this->assignment->getId());

        $this->assignment->setSubjectId(103);
        $this->assertEquals(103, $this->assignment->getSubjectId());

        $this->assignment->setFacultyId(203);
        $this->assertEquals(203, $this->assignment->getFacultyId());

        $this->assignment->setYearLevel('3rd Year');
        $this->assertEquals('3rd Year', $this->assignment->getYearLevel());

        $this->assignment->setSection('C');
        $this->assertEquals('C', $this->assignment->getSection());

        $this->assignment->setAcademicYear('2025-2026');
        $this->assertEquals('2025-2026', $this->assignment->getAcademicYear());

        $this->assignment->setSemester('Summer');
        $this->assertEquals('Summer', $this->assignment->getSemester());

        $this->assignment->setStatus('completed');
        $this->assertEquals('completed', $this->assignment->getStatus());

        $this->assignment->setNotes('Updated notes');
        $this->assertEquals('Updated notes', $this->assignment->getNotes());
    }

    /**
     * @test
     */
    public function it_should_handle_null_and_empty_values_correctly()
    {
        $assignment = new SubjectAssignment([
            'subject_id' => null,
            'faculty_id' => null,
            'year_level' => '',
            'section' => '',
            'academic_year' => '',
            'semester' => '',
            'status' => null,
            'notes' => null
        ]);

        $this->assertNull($assignment->getSubjectId());
        $this->assertNull($assignment->getFacultyId());
        $this->assertEquals('', $assignment->getYearLevel());
        $this->assertEquals('', $assignment->getSection());
        $this->assertEquals('', $assignment->getAcademicYear());
        $this->assertEquals('', $assignment->getSemester());
        $this->assertEquals('active', $assignment->getStatus()); // defaults to 'active'
        $this->assertEquals('', $assignment->getNotes()); // defaults to empty string
    }
}
