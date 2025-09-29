<?php

namespace App\Models;

class Exam
{
    private $id;
    private $title;
    private $description;
    private $subjectId;
    private $facultyId;
    private $yearLevel;
    private $section;
    private $academicYear;
    private $semester;
    private $examType; // quiz, midterm, final, etc.
    private $timeLimit; // in minutes
    private $totalPoints;
    private $instructions;
    private $isActive;
    private $startDate;
    private $endDate;
    private $allowRetakes;
    private $maxAttempts;
    private $showResults;
    private $randomizeQuestions;
    private $createdAt;
    private $updatedAt;

    // Additional fields from joins
    private $questions = [];
    private $subjectCode;
    private $subjectName;
    private $facultyName;

    private const VALID_EXAM_TYPES = ['quiz', 'midterm', 'final', 'assignment', 'project'];
    private const VALID_YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    private const VALID_SEMESTERS = ['1st Semester', '2nd Semester', 'Summer'];
    private const VALID_SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->subjectId = $data['subject_id'] ?? null;
        $this->facultyId = $data['faculty_id'] ?? null;
        $this->yearLevel = $data['year_level'] ?? '';
        $this->section = $data['section'] ?? '';
        $this->academicYear = $data['academic_year'] ?? '';
        $this->semester = $data['semester'] ?? '';
        $this->examType = $data['exam_type'] ?? 'quiz';
        $this->timeLimit = $data['time_limit'] ?? 60;
        $this->totalPoints = $data['total_points'] ?? 0;
        $this->instructions = $data['instructions'] ?? '';
        $this->isActive = $data['is_active'] ?? true;
        $this->startDate = $data['start_date'] ?? null;
        $this->endDate = $data['end_date'] ?? null;
        $this->allowRetakes = $data['allow_retakes'] ?? false;
        $this->maxAttempts = $data['max_attempts'] ?? 1;
        $this->showResults = $data['show_results'] ?? true;
        $this->randomizeQuestions = $data['randomize_questions'] ?? false;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
        
        // Join fields
        $this->subjectCode = $data['subject_code'] ?? null;
        $this->subjectName = $data['subject_name'] ?? null;
        $this->facultyName = $data['faculty_name'] ?? null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getSubjectId() { return $this->subjectId; }
    public function getFacultyId() { return $this->facultyId; }
    public function getYearLevel() { return $this->yearLevel; }
    public function getSection() { return $this->section; }
    public function getAcademicYear() { return $this->academicYear; }
    public function getSemester() { return $this->semester; }
    public function getExamType() { return $this->examType; }
    public function getTimeLimit() { return $this->timeLimit; }
    public function getTotalPoints() { return $this->totalPoints; }
    public function getInstructions() { return $this->instructions; }
    public function getIsActive() { return $this->isActive; }
    public function getStartDate() { return $this->startDate; }
    public function getEndDate() { return $this->endDate; }
    public function getAllowRetakes() { return $this->allowRetakes; }
    public function getMaxAttempts() { return $this->maxAttempts; }
    public function getShowResults() { return $this->showResults; }
    public function getRandomizeQuestions() { return $this->randomizeQuestions; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getSubjectCode() { return $this->subjectCode; }
    public function getSubjectName() { return $this->subjectName; }
    public function getFacultyName() { return $this->facultyName; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
    public function setSubjectId($subjectId) { $this->subjectId = $subjectId; }
    public function setFacultyId($facultyId) { $this->facultyId = $facultyId; }
    public function setYearLevel($yearLevel) { $this->yearLevel = $yearLevel; }
    public function setSection($section) { $this->section = $section; }
    public function setAcademicYear($academicYear) { $this->academicYear = $academicYear; }
    public function setSemester($semester) { $this->semester = $semester; }
    public function setExamType($examType) { $this->examType = $examType; }
    public function setTimeLimit($timeLimit) { $this->timeLimit = $timeLimit; }
    public function setTotalPoints($totalPoints) { $this->totalPoints = $totalPoints; }
    public function setInstructions($instructions) { $this->instructions = $instructions; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    public function setStartDate($startDate) { $this->startDate = $startDate; }
    public function setEndDate($endDate) { $this->endDate = $endDate; }
    public function setAllowRetakes($allowRetakes) { $this->allowRetakes = $allowRetakes; }
    public function setMaxAttempts($maxAttempts) { $this->maxAttempts = $maxAttempts; }
    public function setShowResults($showResults) { $this->showResults = $showResults; }
    public function setRandomizeQuestions($randomizeQuestions) { $this->randomizeQuestions = $randomizeQuestions; }

    /**
     * Validate exam data
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        if (empty($this->title)) {
            $errors[] = "Exam title is required";
        }

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

        if (!empty($this->examType) && !in_array($this->examType, self::VALID_EXAM_TYPES)) {
            $errors[] = "Invalid exam type. Must be one of: " . implode(", ", self::VALID_EXAM_TYPES);
        }

        if ($this->timeLimit !== null && ($this->timeLimit < 1 || $this->timeLimit > 480)) {
            $errors[] = "Time limit must be between 1 and 480 minutes";
        }

        if ($this->maxAttempts !== null && ($this->maxAttempts < 1 || $this->maxAttempts > 10)) {
            $errors[] = "Max attempts must be between 1 and 10";
        }

        // Date validation
        if ($this->startDate && $this->endDate) {
            if (strtotime($this->startDate) >= strtotime($this->endDate)) {
                $errors[] = "End date must be after start date";
            }
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
            'title' => $this->title,
            'description' => $this->description,
            'subject_id' => $this->subjectId,
            'faculty_id' => $this->facultyId,
            'year_level' => $this->yearLevel,
            'section' => $this->section,
            'academic_year' => $this->academicYear,
            'semester' => $this->semester,
            'exam_type' => $this->examType,
            'time_limit' => $this->timeLimit,
            'total_points' => $this->totalPoints,
            'instructions' => $this->instructions,
            'is_active' => $this->isActive,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'allow_retakes' => $this->allowRetakes,
            'max_attempts' => $this->maxAttempts,
            'show_results' => $this->showResults,
            'randomize_questions' => $this->randomizeQuestions,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'subject_code' => $this->subjectCode,
            'subject_name' => $this->subjectName,
            'faculty_name' => $this->facultyName
        ];
    }

    /**
     * Check if exam is currently active
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        $now = time();
        $start = $this->startDate ? strtotime($this->startDate) : 0;
        $end = $this->endDate ? strtotime($this->endDate) : PHP_INT_MAX;

        return $now >= $start && $now <= $end;
    }

    /**
     * Get exam status
     */
    public function getStatus(): string
    {
        if (!$this->isActive) {
            return 'inactive';
        }

        $now = time();
        $start = $this->startDate ? strtotime($this->startDate) : 0;
        $end = $this->endDate ? strtotime($this->endDate) : PHP_INT_MAX;

        if ($now < $start) {
            return 'scheduled';
        } elseif ($now > $end) {
            return 'ended';
        } else {
            return 'active';
        }
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): void
    {
        $this->questions = $questions;
    }
}
