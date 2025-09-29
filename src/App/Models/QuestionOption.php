<?php

namespace App\Models;

class QuestionOption
{
    private $id;
    private $questionId;
    private $optionText;
    private $isCorrect;
    private $orderIndex;
    private $createdAt;
    private $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->questionId = $data['question_id'] ?? null;
        $this->optionText = $data['option_text'] ?? '';
        $this->isCorrect = $data['is_correct'] ?? false;
        $this->orderIndex = $data['order_index'] ?? 0;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getQuestionId() { return $this->questionId; }
    public function getOptionText() { return $this->optionText; }
    public function getIsCorrect() { return $this->isCorrect; }
    public function getOrderIndex() { return $this->orderIndex; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setQuestionId($questionId) { $this->questionId = $questionId; }
    public function setOptionText($optionText) { $this->optionText = $optionText; }
    public function setIsCorrect($isCorrect) { $this->isCorrect = $isCorrect; }
    public function setOrderIndex($orderIndex) { $this->orderIndex = $orderIndex; }

    /**
     * Validate option data
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        if (empty($this->optionText)) {
            $errors[] = "Option text is required";
        }

        if (empty($this->questionId)) {
            $errors[] = "Question ID is required";
        }

        if ($this->orderIndex !== null && $this->orderIndex < 0) {
            $errors[] = "Order index must be non-negative";
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
            'question_id' => $this->questionId,
            'option_text' => $this->optionText,
            'is_correct' => $this->isCorrect,
            'order_index' => $this->orderIndex,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}
