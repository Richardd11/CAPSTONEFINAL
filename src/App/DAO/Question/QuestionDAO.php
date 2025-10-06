<?php

namespace App\DAO\Question;

use App\Models\Question;
use App\Config\Database;
use App\Interfaces\QuestionDAOInterface;
use PDO;

class QuestionDAO implements QuestionDAOInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new question
     */
    public function create(Question $question)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO questions (
                    exam_id, question_text, question_type, points, order_index,
                    is_required, explanation, correct_answer
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $question->getExamId(),
                $question->getQuestionText(),
                $question->getQuestionType(),
                $question->getPoints(),
                $question->getOrderIndex(),
                $question->getIsRequired() ? 1 : 0,
                $question->getExplanation(),
                $question->getCorrectAnswer()
            ]);
            
            if ($result) {
                $question->setId($this->db->lastInsertId());
                return $question;
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Error creating question: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing question
     */
    public function update(Question $question)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE questions SET
                    question_text = ?, question_type = ?, points = ?, order_index = ?,
                    is_required = ?, explanation = ?, correct_answer = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $question->getQuestionText(),
                $question->getQuestionType(),
                $question->getPoints(),
                $question->getOrderIndex(),
                $question->getIsRequired() ? 1 : 0,
                $question->getExplanation(),
                $question->getCorrectAnswer(),
                $question->getId()
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating question: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a question
     */
    public function delete($questionId)
    {
        try {
            // First delete question options
            $stmt = $this->db->prepare("DELETE FROM question_options WHERE question_id = ?");
            $stmt->execute([$questionId]);
            
            // Then delete the question
            $stmt = $this->db->prepare("DELETE FROM questions WHERE id = ?");
            return $stmt->execute([$questionId]);
        } catch (\PDOException $e) {
            error_log("Error deleting question: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete questions by exam ID
     */
    public function deleteByExamId($examId)
    {
        try {
            // First delete all question options for questions in this exam
            $stmt = $this->db->prepare("
                DELETE qo FROM question_options qo
                INNER JOIN questions q ON qo.question_id = q.id
                WHERE q.exam_id = ?
            ");
            $stmt->execute([$examId]);
            
            // Then delete all questions for this exam
            $stmt = $this->db->prepare("DELETE FROM questions WHERE exam_id = ?");
            return $stmt->execute([$examId]);
        } catch (\PDOException $e) {
            error_log("Error deleting questions by exam ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get question by ID
     */
    public function getById($questionId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM questions WHERE id = ?");
            $stmt->execute([$questionId]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new Question($row) : null;
        } catch (\PDOException $e) {
            error_log("Error getting question by ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get questions by exam ID
     */
    public function getByExamId($examId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM questions 
                WHERE exam_id = ? 
                ORDER BY order_index ASC
            ");
            $stmt->execute([$examId]);
            
            $questions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $questions[] = new Question($row);
            }
            
            return $questions;
        } catch (\PDOException $e) {
            error_log("Error getting questions by exam ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all questions
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM questions 
                ORDER BY exam_id, order_index ASC
            ");
            $stmt->execute();
            
            $questions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $questions[] = new Question($row);
            }
            
            return $questions;
        } catch (\PDOException $e) {
            error_log("Error getting all questions: " . $e->getMessage());
            throw $e;
        }
    }
}
