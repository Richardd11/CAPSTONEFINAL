<?php

namespace App\Models;

class Subject
{
    private $subject_id;
    private $subject_code;
    private $subject_name;
    private $description;
    private $units;
    private $year_level;
    private $semester;
    private $created_at;
    private $updated_at;

    private const VALID_YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    private const VALID_SEMESTERS = ['1st Semester', '2nd Semester', 'Summer'];
    private const MIN_UNITS = 1;
    private const MAX_UNITS = 6;

    public function __construct(array $data = [])
    {
        $this->subject_id = $data['subject_id'] ?? null;
        $this->subject_code = $data['subject_code'] ?? '';
        $this->subject_name = $data['subject_name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->units = $data['units'] ?? 3;
        $this->year_level = $data['year_level'] ?? '';
        $this->semester = $data['semester'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    // Getters
    public function getSubjectId()
    {
        return $this->subject_id;
    }

    public function getSubjectCode()
    {
        return $this->subject_code;
    }

    public function getSubjectName()
    {
        return $this->subject_name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getUnits()
    {
        return $this->units;
    }

    public function getYearLevel()
    {
        return $this->year_level;
    }

    public function getSemester()
    {
        return $this->semester;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Validate subject data
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        if (empty($this->subject_code)) {
            $errors[] = "Subject code is required";
        } elseif (!preg_match('/^[A-Z0-9]{3,10}$/', $this->subject_code)) {
            $errors[] = "Subject code must be 3-10 characters and contain only uppercase letters and numbers";
        }

        if (empty($this->subject_name)) {
            $errors[] = "Subject name is required";
        }

        // Units validation
        if (!is_numeric($this->units)) {
            $errors[] = "Units must be a number";
        } elseif ($this->units <= 0) {
            $errors[] = "Units must be greater than 0";
        } elseif ($this->units > self::MAX_UNITS) {
            $errors[] = "Units must be between " . self::MIN_UNITS . " and " . self::MAX_UNITS;
        }

        // Year level validation
        if (empty($this->year_level)) {
            $errors[] = "Year level is required";
        } elseif (!in_array($this->year_level, self::VALID_YEAR_LEVELS)) {
            $errors[] = "Invalid year level. Must be one of: " . implode(", ", self::VALID_YEAR_LEVELS);
        }

        // Semester validation
        if (empty($this->semester)) {
            $errors[] = "Semester is required";
        } elseif (!in_array($this->semester, self::VALID_SEMESTERS)) {
            $errors[] = "Invalid semester. Must be one of: " . implode(", ", self::VALID_SEMESTERS);
        }

        return $errors;
    }

    /**
     * Convert object to array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'subject_id' => $this->subject_id,
            'subject_code' => $this->subject_code,
            'subject_name' => $this->subject_name,
            'description' => $this->description,
            'units' => $this->units,
            'year_level' => $this->year_level,
            'semester' => $this->semester,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    // Setters
    public function setSubjectId($subject_id)
    {
        $this->subject_id = $subject_id;
        return $this;
    }

    public function setSubjectCode($subject_code)
    {
        $this->subject_code = $subject_code;
        return $this;
    }

    public function setSubjectName($subject_name)
    {
        $this->subject_name = $subject_name;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setUnits($units)
    {
        $this->units = $units;
        return $this;
    }

    public function setYearLevel($year_level)
    {
        $this->year_level = $year_level;
        return $this;
    }

    public function setSemester($semester)
    {
        $this->semester = $semester;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    // Convert to array
    // Duplicate toArray() method removed to fix redeclaration error.

    // Validation method removed to fix redeclaration error.
}