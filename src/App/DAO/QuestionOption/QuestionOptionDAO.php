<?php

namespace App\DAO\QuestionOption;

use App\Models\QuestionOption;
use App\Config\Database;
use App\Interfaces\QuestionOptionDAOInterface;
use PDO;

class QuestionOptionDAO implements QuestionOptionDAOInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new question option
     */
    public function create(QuestionOption $option)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO question_options (
                    question_id, option_text, is_correct, order_index
                ) VALUES (?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $option->getQuestionId(),
                $option->getOptionText(),
                $option->getIsCorrect() ? 1 : 0,
                $option->getOrderIndex()
            ]);
            
            if ($result) {
                $option->setId($this->db->lastInsertId());
                return $option;
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Error creating question option: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing question option
     */
    public function update(QuestionOption $option)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE question_options SET
                    option_text = ?, is_correct = ?, order_index = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $option->getOptionText(),
                $option->getIsCorrect() ? 1 : 0,
                $option->getOrderIndex(),
                $option->getId()
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating question option: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a question option
     */
    public function delete($optionId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM question_options WHERE id = ?");
            return $stmt->execute([$optionId]);
        } catch (\PDOException $e) {
            error_log("Error deleting question option: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete options by question ID
     */
    public function deleteByQuestionId($questionId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM question_options WHERE question_id = ?");
            return $stmt->execute([$questionId]);
        } catch (\PDOException $e) {
            error_log("Error deleting options by question ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get option by ID
     */
    public function getById($optionId)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM question_options WHERE id = ?");
            $stmt->execute([$optionId]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new QuestionOption($row) : null;
        } catch (\PDOException $e) {
            error_log("Error getting option by ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get options by question ID
     */
    public function getByQuestionId($questionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM question_options 
                WHERE question_id = ? 
                ORDER BY order_index ASC
            ");
            $stmt->execute([$questionId]);
            
            $options = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[] = new QuestionOption($row);
            }
            
            return $options;
        } catch (\PDOException $e) {
            error_log("Error getting options by question ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all options
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM question_options 
                ORDER BY question_id, order_index ASC
            ");
            $stmt->execute();
            
            $options = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options[] = new QuestionOption($row);
            }
            
            return $options;
        } catch (\PDOException $e) {
            error_log("Error getting all options: " . $e->getMessage());
            throw $e;
        }
    }
}
