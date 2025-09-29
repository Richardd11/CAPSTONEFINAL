<?php

namespace App\Interfaces;

use App\Models\Question;

interface QuestionDAOInterface
{
    /**
     * Create a new question
     * @param Question $question
     * @return Question|null
     */
    public function create(Question $question);

    /**
     * Update an existing question
     * @param Question $question
     * @return bool
     */
    public function update(Question $question);

    /**
     * Delete a question
     * @param int $questionId
     * @return bool
     */
    public function delete($questionId);

    /**
     * Delete questions by exam ID
     * @param int $examId
     * @return bool
     */
    public function deleteByExamId($examId);

    /**
     * Get question by ID
     * @param int $questionId
     * @return Question|null
     */
    public function getById($questionId);

    /**
     * Get questions by exam ID
     * @param int $examId
     * @return array
     */
    public function getByExamId($examId);

    /**
     * Get all questions
     * @return array
     */
    public function getAll();
}
