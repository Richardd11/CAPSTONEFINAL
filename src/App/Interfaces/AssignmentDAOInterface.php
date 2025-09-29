<?php

namespace App\Interfaces;

use App\Models\SubjectAssignment;

interface AssignmentDAOInterface
{
    /**
     * Get all assignments
     * @return array
     */
    public function getAll();

    /**
     * Get assignment by ID
     * @param int $id
     * @return SubjectAssignment|null
     */
    public function getById($id);

    /**
     * Create new assignment
     * @param SubjectAssignment $assignment
     * @return int|false Returns new assignment ID or false on failure
     */
    public function create(SubjectAssignment $assignment);

    /**
     * Update existing assignment
     * @param SubjectAssignment $assignment
     * @return bool
     */
    public function update(SubjectAssignment $assignment);

    /**
     * Delete assignment
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Check if assignment exists
     * @param int $subjectId
     * @param string $yearLevel
     * @param string $section
     * @param string $academicYear
     * @param string $semester
     * @param int|null $excludeId ID to exclude when checking (for updates)
     * @return bool
     */
    public function assignmentExists($subjectId, $yearLevel, $section, $academicYear, $semester, $excludeId = null);

    /**
     * Get faculty assignments
     * @param int $facultyId
     * @param string|null $academicYear
     * @return array
     */
    public function getFacultyAssignments($facultyId, $academicYear = null);

    /**
     * Get unassigned subjects
     * @param string $academicYear
     * @param string $semester
     * @return array
     */
    public function getUnassignedSubjects($academicYear, $semester);

    /**
     * Get assignments by filters
     * @param array $filters
     * @return array
     */
    public function getByFilters($filters = []);

    /**
     * Get faculty workload
     * @param int $facultyId
     * @param string|null $academicYear
     * @return array
     */
    public function getFacultyWorkload($facultyId, $academicYear = null);

    /**
     * Get assignment statistics
     * @param string|null $academicYear
     * @return array
     */
    public function getAssignmentStats($academicYear = null);
}
