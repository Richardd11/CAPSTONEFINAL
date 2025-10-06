<?php

namespace App\Models;

class Question
{
    private $id;
    private $examId;
    private $questionText;
    private $questionType; // multiple_choice, true_false, short_answer, essay, fill_blank
    private $points;
    private $orderIndex;
    private $isRequired;
    private $explanation;
    private $correctAnswer; // For true_false and other question types
    private $createdAt;
    private $updatedAt;
    
    // Additional fields for relationships
    private $options = [];

    private const VALID_QUESTION_TYPES = [
        'multiple_choice',
        'true_false', 
        'short_answer',
        'essay',
        'fill_blank',
        'matching',
        'dropdown'
    ];

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->examId = $data['exam_id'] ?? null;
        $this->questionText = $data['question_text'] ?? '';
        $this->questionType = $data['question_type'] ?? 'multiple_choice';
        $this->points = $data['points'] ?? 1;
        $this->orderIndex = $data['order_index'] ?? 0;
        $this->isRequired = $data['is_required'] ?? true;
        $this->explanation = $data['explanation'] ?? '';
        $this->correctAnswer = $data['correct_answer'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getExamId() { return $this->examId; }
    public function getQuestionText() { return $this->questionText; }
    public function getQuestionType() { return $this->questionType; }
    public function getPoints() { return $this->points; }
    public function getOrderIndex() { return $this->orderIndex; }
    public function getIsRequired() { return $this->isRequired; }
    public function getExplanation() { return $this->explanation; }
    public function getCorrectAnswer() { return $this->correctAnswer; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setExamId($examId) { $this->examId = $examId; }
    public function setQuestionText($questionText) { $this->questionText = $questionText; }
    public function setQuestionType($questionType) { $this->questionType = $questionType; }
    public function setPoints($points) { $this->points = $points; }
    public function setOrderIndex($orderIndex) { $this->orderIndex = $orderIndex; }
    public function setIsRequired($isRequired) { $this->isRequired = $isRequired; }
    public function setExplanation($explanation) { $this->explanation = $explanation; }
    public function setCorrectAnswer($correctAnswer) { $this->correctAnswer = $correctAnswer; }

    /**
     * Validate question data
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        // Required fields
        if (empty($this->questionText)) {
            $errors[] = "Question text is required";
        }

        if (empty($this->examId)) {
            $errors[] = "Exam ID is required";
        }

        if (!in_array($this->questionType, self::VALID_QUESTION_TYPES)) {
            $errors[] = "Invalid question type. Must be one of: " . implode(", ", self::VALID_QUESTION_TYPES);
        }

        if ($this->points !== null && ($this->points < 0 || $this->points > 100)) {
            $errors[] = "Points must be between 0 and 100";
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
            'exam_id' => $this->examId,
            'question_text' => $this->questionText,
            'question_type' => $this->questionType,
            'points' => $this->points,
            'order_index' => $this->orderIndex,
            'is_required' => $this->isRequired,
            'explanation' => $this->explanation,
            'correct_answer' => $this->correctAnswer,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    /**
     * Get available question types
     */
    public static function getQuestionTypes(): array
    {
        return [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'short_answer' => 'Short Answer',
            'essay' => 'Essay',
            'fill_blank' => 'Fill in the Blank',
            'matching' => 'Matching',
            'dropdown' => 'Dropdown'
        ];
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
