<?php

namespace App\Models;

class SubjectAssignment
{
    private $id;
    private $subjectId;
    private $facultyId;
    private $yearLevel;
    private $section;
    private $academicYear;
    private $semester;
    private $status;
    private $notes;
    
    // Additional fields from joins
    private $subjectCode;
    private $subjectName;
    private $facultyName;

    private const VALID_YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    private const VALID_SEMESTERS = ['1st Semester', '2nd Semester', 'Summer'];
    private const VALID_STATUSES = ['active', 'inactive', 'completed', 'cancelled'];
    private const VALID_SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];
    private $createdAt;
    private $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->subjectId = $data['subject_id'] ?? null;
        $this->facultyId = $data['faculty_id'] ?? null;
        $this->yearLevel = $data['year_level'] ?? '';
        $this->section = $data['section'] ?? '';
        $this->academicYear = $data['academic_year'] ?? '';
        $this->semester = $data['semester'] ?? '';
        $this->status = $data['status'] ?? 'active';
        $this->notes = $data['notes'] ?? '';
        $this->subjectCode = $data['subject_code'] ?? null;
        $this->subjectName = $data['subject_name'] ?? null;
        $this->facultyName = $data['faculty_name'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getSubjectId() { return $this->subjectId; }
    public function getFacultyId() { return $this->facultyId; }
    public function getYearLevel() { return $this->yearLevel; }
    public function getSection() { return $this->section; }
    public function getAcademicYear() { return $this->academicYear; }
    public function getSemester() { return $this->semester; }
    public function getStatus() { return $this->status; }
    public function getNotes() { return $this->notes; }
    public function getSubjectCode() { return $this->subjectCode; }
    public function getSubjectName() { return $this->subjectName; }
    public function getFacultyName() { return $this->facultyName; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setSubjectId($subjectId) { $this->subjectId = $subjectId; }
    public function setFacultyId($facultyId) { $this->facultyId = $facultyId; }
    public function setYearLevel($yearLevel) { $this->yearLevel = $yearLevel; }
    public function setSection($section) { $this->section = $section; }
    public function setAcademicYear($academicYear) { $this->academicYear = $academicYear; }
    public function setSemester($semester) { $this->semester = $semester; }
    public function setStatus($status) { $this->status = $status; }
    public function setNotes($notes) { $this->notes = $notes; }

    /**
     * Validate assignment data
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        if (empty($this->subjectId)) {
            $errors[] = "Subject ID is required";
        }

        if (empty($this->facultyId)) {
            $errors[] = "Faculty ID is required";
        }

        if (empty($this->yearLevel)) {
            $errors[] = "Year level is required";
        } elseif (!in_array($this->yearLevel, self::VALID_YEAR_LEVELS)) {
            $errors[] = "Invalid year level. Must be one of: " . implode(", ", self::VALID_YEAR_LEVELS);
        }

        if (empty($this->section)) {
            $errors[] = "Section is required";
        } elseif (!in_array($this->section, self::VALID_SECTIONS)) {
            $errors[] = "Invalid section. Must be one of: " . implode(", ", self::VALID_SECTIONS);
        }

        if (empty($this->academicYear)) {
            $errors[] = "Academic year is required";
        } elseif (!preg_match('/^\d{4}-\d{4}$/', $this->academicYear)) {
            $errors[] = "Invalid academic year format. Must be YYYY-YYYY";
        }

        if (empty($this->semester)) {
            $errors[] = "Semester is required";
        } elseif (!in_array($this->semester, self::VALID_SEMESTERS)) {
            $errors[] = "Invalid semester. Must be one of: " . implode(", ", self::VALID_SEMESTERS);
        }

        if (!empty($this->status) && !in_array($this->status, self::VALID_STATUSES)) {
            $errors[] = "Invalid status. Must be one of: " . implode(", ", self::VALID_STATUSES);
        }

        return $errors;
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subjectId,
            'faculty_id' => $this->facultyId,
            'year_level' => $this->yearLevel,
            'section' => $this->section,
            'academic_year' => $this->academicYear,
            'semester' => $this->semester,
            'status' => $this->status,
            'notes' => $this->notes,
            'subject_code' => $this->subjectCode,
            'subject_name' => $this->subjectName,
            'faculty_name' => $this->facultyName,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    /**
     * Validate assignment data
     */
    

    /**
     * Check if assignment is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get assignment key for uniqueness check
     */
    public function getAssignmentKey(): string
    {
        return $this->subjectId . '_' . $this->yearLevel . '_' . $this->section . '_' . $this->academicYear . '_' . $this->semester;
    }
}