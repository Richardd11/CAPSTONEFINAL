<?php

namespace App\Interfaces;

use App\Models\Exam;

interface ExamDAOInterface
{
    /**
     * Create a new exam
     * @param Exam $exam
     * @return Exam|null
     */
    public function create(Exam $exam);

    /**
     * Update an existing exam
     * @param Exam $exam
     * @return bool
     */
    public function update(Exam $exam);

    /**
     * Delete an exam
     * @param int $examId
     * @return bool
     */
    public function delete($examId);

    /**
     * Get exam by ID
     * @param int $examId
     * @return Exam|null
     */
    public function getById($examId);

    /**
     * Get all exams
     * @return array
     */
    public function getAll();

    /**
     * Get exams by faculty
     * @param int $facultyId
     * @return array
     */
    public function getByFaculty($facultyId);

    /**
     * Get exams by filters
     * @param array $filters
     * @return array
     */
    public function getByFilters($filters = []);

    /**
     * Get exam statistics
     * @param int|null $facultyId
     * @return array
     */
    public function getExamStats($facultyId = null);
}
