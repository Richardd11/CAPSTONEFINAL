<?php

namespace App\Interfaces;

interface ExamServiceInterface
{
    /**
     * Create a new exam
     * @param array $data
     * @return array
     */
    public function createExam($data);

    /**
     * Update an existing exam
     * @param int $examId
     * @param array $data
     * @return array
     */
    public function updateExam($examId, $data);

    /**
     * Delete an exam
     * @param int $examId
     * @return array
     */
    public function deleteExam($examId);

    /**
     * Get exam by ID
     * @param int $examId
     * @return \App\Models\Exam|null
     */
    public function getExamById($examId);

    /**
     * Get exams by faculty
     * @param int $facultyId
     * @return array
     */
    public function getExamsByFaculty($facultyId);

    /**
     * Get exam questions
     * @param int $examId
     * @return array
     */
    public function getExamQuestions($examId);

    /**
     * Get question options
     * @param int $questionId
     * @return array
     */
    public function getQuestionOptions($questionId);

    /**
     * Get exams by filters
     * @param array $filters
     * @return array
     */
    public function getExamsByFilters($filters = []);

    /**
     * Get exam statistics
     * @param int|null $facultyId
     * @return array
     */
    public function getExamStats($facultyId = null);
}
