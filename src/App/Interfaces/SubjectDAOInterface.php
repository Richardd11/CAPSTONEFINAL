<?php

namespace App\Interfaces;

use App\Models\Subject;

interface SubjectDAOInterface
{
    /**
     * Get all subjects
     * @return array
     */
    public function getAll();

    /**
     * Get subject by ID
     * @param int $id
     * @return Subject|null
     */
    public function getById($id);

    /**
     * Get subject by code
     * @param string $code
     * @return Subject|null
     */
    public function getByCode($code);

    /**
     * Create new subject
     * @param Subject $subject
     * @return Subject|null Returns the created subject with ID set, or null on failure
     */
    public function create(Subject $subject);

    /**
     * Update existing subject
     * @param Subject $subject
     * @return bool
     */
    public function update(Subject $subject);

    /**
     * Delete subject
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Check if subject has faculty assignments
     * @param int $id
     * @return bool
     */
    public function hasFacultyAssignments($id);

    /**
     * Check if subject has exams
     * @param int $id
     * @return bool
     */
    public function hasExams($id);

    /**
     * Get subjects by year level
     * @param string $yearLevel
     * @return array
     */
    public function getByYearLevel($yearLevel);

    /**
     * Get subjects by semester
     * @param string $semester
     * @return array
     */
    public function getBySemester($semester);

    /**
     * Search subjects by query
     * @param string $query
     * @return array
     */
    public function search($query);
}
