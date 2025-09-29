<?php

namespace App\Interfaces;

interface SubjectServiceInterface
{
    /**
     * Get all subjects
     * @return array
     */
    public function getAllSubjects();

    /**
     * Get subject by ID
     * @param int $id
     * @return array|null
     */
    public function getSubjectById($id);

    /**
     * Create new subject
     * @param array $data
     * @return array Response with success status and message
     */
    public function createSubject(array $data);

    /**
     * Update existing subject
     * @param int $id
     * @param array $data
     * @return array Response with success status and message
     */
    public function updateSubject($id, array $data);

    /**
     * Delete subject
     * @param int $id
     * @return array Response with success status and message
     */
    public function deleteSubject($id);

    /**
     * Search subjects
     * @param string $query
     * @return array
     */
    public function searchSubjects($query);

    /**
     * Get subjects by year level
     * @param string $yearLevel
     * @return array
     */
    public function getSubjectsByYearLevel($yearLevel);

    /**
     * Get subjects by semester
     * @param string $semester
     * @return array
     */
    public function getSubjectsBySemester($semester);

    /**
     * Get year levels for dropdown
     * @return array
     */
    public function getYearLevels();

    /**
     * Get semesters for dropdown
     * @return array
     */
    public function getSemesters();
}
