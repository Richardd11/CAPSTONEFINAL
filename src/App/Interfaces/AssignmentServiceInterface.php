<?php

namespace App\Interfaces;

use App\Models\SubjectAssignment;

interface AssignmentServiceInterface
{
    /**
     * Get all assignments
     * @return array
     */
    public function getAllAssignments();

    /**
     * Get assignment by ID
     * @param int $id
     * @return array|null
     */
    public function getAssignmentById($id);

    /**
     * Create new assignment
     * @param array $data
     * @return array Response with success status and message
     */
    public function createAssignment(array $data);

    /**
     * Update existing assignment
     * @param int $id
     * @param array $data
     * @return array Response with success status and message
     */
    public function updateAssignment($id, array $data);

    /**
     * Delete assignment
     * @param int $id
     * @return array Response with success status and message
     */
    public function deleteAssignment($id);

    /**
     * Get faculty workload
     * @param int $facultyId
     * @param string|null $academicYear
     * @return array
     */
    public function getFacultyWorkload($facultyId, $academicYear = null);

    /**
     * Get unassigned subjects
     * @param string $academicYear
     * @param string $semester
     * @return array
     */
    public function getUnassignedSubjects($academicYear, $semester);

    /**
     * Get assignment statistics
     * @param string|null $academicYear
     * @return array
     */
    public function getAssignmentStats($academicYear = null);

    /**
     * Check for faculty schedule conflicts
     * @param array $assignmentData
     * @param int|null $excludeId ID of current assignment when updating
     * @return array Response with conflict status and details
     */
    public function checkScheduleConflicts(array $assignmentData, $excludeId = null);

    /**
     * Get assignments by filters
     * @param array $filters
     * @return array
     */
    public function getAssignmentsByFilters($filters = []);
}
