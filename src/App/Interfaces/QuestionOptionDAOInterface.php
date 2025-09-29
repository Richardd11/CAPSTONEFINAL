<?php

namespace App\Interfaces;

use App\Models\QuestionOption;

interface QuestionOptionDAOInterface
{
    /**
     * Create a new question option
     * @param QuestionOption $option
     * @return QuestionOption|null
     */
    public function create(QuestionOption $option);

    /**
     * Update an existing question option
     * @param QuestionOption $option
     * @return bool
     */
    public function update(QuestionOption $option);

    /**
     * Delete a question option
     * @param int $optionId
     * @return bool
     */
    public function delete($optionId);

    /**
     * Delete options by question ID
     * @param int $questionId
     * @return bool
     */
    public function deleteByQuestionId($questionId);

    /**
     * Get option by ID
     * @param int $optionId
     * @return QuestionOption|null
     */
    public function getById($optionId);

    /**
     * Get options by question ID
     * @param int $questionId
     * @return array
     */
    public function getByQuestionId($questionId);

    /**
     * Get all options
     * @return array
     */
    public function getAll();
}
