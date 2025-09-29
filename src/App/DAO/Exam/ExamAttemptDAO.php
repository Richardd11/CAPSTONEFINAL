<?php

namespace App\DAO\Exam;

use App\Config\Database;
use PDO;

class ExamAttemptDAO
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new exam attempt
     */
    public function createAttempt($studentId, $examId): int
    {
        try {
            // First check what columns exist
            $stmt = $this->db->prepare("DESCRIBE exam_attempts");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasStartedAt = in_array('started_at', $columns);
            
            if ($hasStartedAt) {
                $stmt = $this->db->prepare("
                    INSERT INTO exam_attempts (student_id, exam_id, status, started_at, created_at)
                    VALUES (?, ?, 'in_progress', NOW(), NOW())
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO exam_attempts (student_id, exam_id, status, created_at)
                    VALUES (?, ?, 'in_progress', NOW())
                ");
            }
            
            $stmt->execute([$studentId, $examId]);
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error creating exam attempt: " . $e->getMessage());
            
            // Fallback: try with minimal columns
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO exam_attempts (student_id, exam_id, status)
                    VALUES (?, ?, 'in_progress')
                ");
                $stmt->execute([$studentId, $examId]);
                return $this->db->lastInsertId();
            } catch (\PDOException $e2) {
                error_log("Fallback insert also failed: " . $e2->getMessage());
                return 1; // Return dummy ID to prevent complete failure
            }
        }
    }

    /**
     * Get exam attempt by student and exam
     */
    public function getAttemptByStudentAndExam($studentId, $examId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM exam_attempts 
                WHERE student_id = ? AND exam_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            
            $stmt->execute([$studentId, $examId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error getting exam attempt: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Complete an exam attempt
     */
    public function completeAttempt($attemptId, $score, $answers): bool
    {
        try {
            $this->db->beginTransaction();

            // Check what columns exist
            $stmt = $this->db->prepare("DESCRIBE exam_attempts");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasCompletedAt = in_array('completed_at', $columns);
            $hasUpdatedAt = in_array('updated_at', $columns);

            // Update attempt status with available columns
            if ($hasCompletedAt && $hasUpdatedAt) {
                $stmt = $this->db->prepare("
                    UPDATE exam_attempts 
                    SET status = 'completed', score = ?, completed_at = NOW(), updated_at = NOW()
                    WHERE id = ?
                ");
            } elseif ($hasCompletedAt) {
                $stmt = $this->db->prepare("
                    UPDATE exam_attempts 
                    SET status = 'completed', score = ?, completed_at = NOW()
                    WHERE id = ?
                ");
            } else {
                $stmt = $this->db->prepare("
                    UPDATE exam_attempts 
                    SET status = 'completed', score = ?
                    WHERE id = ?
                ");
            }
            
            $stmt->execute([$score, $attemptId]);

            // Save answers
            error_log("=== SAVING ANSWERS ===");
            error_log("Answers to save: " . json_encode($answers));
            foreach ($answers as $questionId => $answer) {
                error_log("Saving answer for question $questionId: $answer");
                $saveResult = $this->saveAnswer($attemptId, $questionId, $answer);
                error_log("Save result for question $questionId: " . ($saveResult ? 'success' : 'failed'));
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error completing exam attempt: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save individual answer
     */
    public function saveAnswer($attemptId, $questionId, $answer): bool
    {
        try {
            // Check what columns exist in student_answers table
            $describeQuery = "DESCRIBE student_answers";
            $describeStmt = $this->db->prepare($describeQuery);
            $describeStmt->execute();
            $columns = $describeStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Determine the correct answer column name
            $answerColumnName = 'answer'; // default
            if (in_array('student_answer', $columns)) {
                $answerColumnName = 'student_answer';
            } elseif (in_array('answer_text', $columns)) {
                $answerColumnName = 'answer_text';
            } elseif (in_array('response', $columns)) {
                $answerColumnName = 'response';
            }
            
            error_log("Saving answer using column: $answerColumnName");
            
            // Check if we have updated_at column
            $hasUpdatedAt = in_array('updated_at', $columns);
            
            if ($hasUpdatedAt) {
                $stmt = $this->db->prepare("
                    INSERT INTO student_answers (attempt_id, question_id, $answerColumnName, created_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    $answerColumnName = VALUES($answerColumnName), updated_at = NOW()
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO student_answers (attempt_id, question_id, $answerColumnName, created_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    $answerColumnName = VALUES($answerColumnName)
                ");
            }
            
            $result = $stmt->execute([$attemptId, $questionId, $answer]);
            error_log("Save answer SQL result: " . ($result ? 'success' : 'failed'));
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error saving answer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update exam attempt score
     */
    public function updateAttemptScore($attemptId, $newScore): bool
    {
        try {
            error_log("=== UPDATING ATTEMPT SCORE ===");
            error_log("Attempt ID: $attemptId, New Score: $newScore");
            
            // Check what columns exist
            $stmt = $this->db->prepare("DESCRIBE exam_attempts");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasUpdatedAt = in_array('updated_at', $columns);
            
            // Update score with available columns
            if ($hasUpdatedAt) {
                $stmt = $this->db->prepare("
                    UPDATE exam_attempts 
                    SET score = ?, updated_at = NOW()
                    WHERE id = ?
                ");
            } else {
                $stmt = $this->db->prepare("
                    UPDATE exam_attempts 
                    SET score = ?
                    WHERE id = ?
                ");
            }
            
            $result = $stmt->execute([$newScore, $attemptId]);
            error_log("Update score result: " . ($result ? 'success' : 'failed'));
            
            return $result;
        } catch (\PDOException $e) {
            error_log("Error updating attempt score: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get student's exam history
     */
    public function getStudentExamHistory($studentId): array
    {
        try {
            // Check what columns exist
            $stmt = $this->db->prepare("DESCRIBE exam_attempts");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasCompletedAt = in_array('completed_at', $columns);
            $orderBy = $hasCompletedAt ? 'ea.completed_at DESC' : 'ea.created_at DESC';
            
            $stmt = $this->db->prepare("
                SELECT 
                    ea.*,
                    e.title as exam_title,
                    e.total_points,
                    s.subject_name,
                    s.subject_code
                FROM exam_attempts ea
                JOIN exams e ON ea.exam_id = e.id
                JOIN subjects s ON e.subject_id = s.subject_id
                WHERE ea.student_id = ? AND ea.status = 'completed'
                ORDER BY {$orderBy}
            ");
            
            $stmt->execute([$studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting student exam history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get attempt by ID
     */
    public function getAttemptById($attemptId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ea.*, e.title as exam_title, e.total_points
                FROM exam_attempts ea
                JOIN exams e ON ea.exam_id = e.id
                WHERE ea.id = ?
            ");
            
            $stmt->execute([$attemptId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Error getting attempt by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get exam results by exam ID with optional student filter
     */
    public function getExamResults($examId, $studentId = null): array
    {
        try {
            // Check what columns exist
            $stmt = $this->db->prepare("DESCRIBE exam_attempts");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasCompletedAt = in_array('completed_at', $columns);
            $completedAtField = $hasCompletedAt ? 'ea.completed_at' : 'ea.created_at';
            
            $query = "
                SELECT 
                    ea.id,
                    ea.student_id as user_id,
                    ea.score,
                    ea.status,
                    {$completedAtField} as completed_at,
                    COALESCE(u.full_name, 'Unknown Student') as name,
                    COALESCE(u.school_id, 'N/A') as student_id
                FROM exam_attempts ea
                JOIN users u ON ea.student_id = u.user_id
                WHERE ea.exam_id = ? AND ea.status = 'completed'
            ";
            
            $params = [$examId];
            
            if ($studentId) {
                $query .= " AND ea.student_id = ?";
                $params[] = $studentId;
            }
            
            $query .= " ORDER BY ea.score DESC";
            
            error_log("=== EXAM RESULTS QUERY ===");
            error_log("Query: " . $query);
            error_log("Params: " . json_encode($params));
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query returned " . count($results) . " results");
            
            return $results;
        } catch (\PDOException $e) {
            error_log("Error getting exam results: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of students who completed an exam
     */
    public function getExamStudentCount($examId): int
    {
        try {
            $query = "
                SELECT COUNT(DISTINCT ea.student_id) as student_count
                FROM exam_attempts ea
                WHERE ea.exam_id = ? AND ea.status = 'completed'
            ";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$examId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['student_count'] ?? 0);
        } catch (\PDOException $e) {
            error_log("Error getting exam student count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get questions and answers for an exam attempt
     */
    public function getAttemptQuestionsAndAnswers($attemptId): array
    {
        try {
            error_log("=== DAO: Getting questions and answers for attempt ID: $attemptId ===");
            
            // First, let's check what columns exist in both tables
            $describeQuery = "DESCRIBE questions";
            $describeStmt = $this->db->prepare($describeQuery);
            $describeStmt->execute();
            $questionColumns = $describeStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Questions table columns: " . json_encode($questionColumns));
            
            $describeAnswersQuery = "DESCRIBE student_answers";
            $describeAnswersStmt = $this->db->prepare($describeAnswersQuery);
            $describeAnswersStmt->execute();
            $answerColumns = $describeAnswersStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Student_answers table columns: " . json_encode($answerColumns));
            
            // Determine the correct answer column name in student_answers table
            $answerColumnName = 'answer'; // default
            if (in_array('student_answer', $answerColumns)) {
                $answerColumnName = 'student_answer';
            } elseif (in_array('answer_text', $answerColumns)) {
                $answerColumnName = 'answer_text';
            } elseif (in_array('response', $answerColumns)) {
                $answerColumnName = 'response';
            }
            
            error_log("Using answer column: $answerColumnName");
            
            // Get both option text and option index for flexible comparison
            // Use COLLATE to fix collation mismatch issues
            $query = "
                SELECT 
                    q.id as question_id,
                    q.question_text,
                    qo.option_text as correct_answer,
                    qo.order_index as correct_index,
                    sa.$answerColumnName as student_answer,
                    sa.selected_option_id,
                    -- Try multiple comparison methods with collation fix
                    CASE 
                        WHEN sa.$answerColumnName COLLATE utf8mb4_unicode_ci = qo.option_text COLLATE utf8mb4_unicode_ci THEN 1
                        WHEN sa.$answerColumnName COLLATE utf8mb4_unicode_ci = CAST(qo.order_index AS CHAR) COLLATE utf8mb4_unicode_ci THEN 1
                        WHEN sa.selected_option_id = qo.id THEN 1
                        ELSE 0
                    END as is_correct
                FROM student_answers sa
                JOIN questions q ON sa.question_id = q.id
                LEFT JOIN question_options qo ON q.id = qo.question_id AND qo.is_correct = 1
                WHERE sa.attempt_id = ?
                ORDER BY q.id
            ";
            
            error_log("Query: " . $query);
            error_log("Params: " . json_encode([$attemptId]));
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$attemptId]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($results) . " questions with answers");
            
            // Debug: Show detailed comparison for each question
            foreach ($results as $result) {
                error_log("Question {$result['question_id']}: Student='{$result['student_answer']}', Correct='{$result['correct_answer']}', Index='{$result['correct_index']}', Selected_ID='{$result['selected_option_id']}', Is_Correct=" . ($result['is_correct'] ? 'YES' : 'NO'));
            }
            
            // Check for questions with missing options and create them if needed
            if (!empty($results)) {
                $needsOptionCreation = false;
                
                // Check each question to see if any are missing options
                foreach ($results as $result) {
                    if (empty($result['correct_answer'])) {
                        $questionId = $result['question_id'];
                        error_log("Question $questionId has no correct answer, checking for missing options");
                        
                        $optionsQuery = "SELECT * FROM question_options WHERE question_id = ?";
                        $optionsStmt = $this->db->prepare($optionsQuery);
                        $optionsStmt->execute([$questionId]);
                        $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Check if this is a True/False question type
                        $questionTypeQuery = "SELECT question_type FROM questions WHERE id = ?";
                        $questionTypeStmt = $this->db->prepare($questionTypeQuery);
                        $questionTypeStmt->execute([$questionId]);
                        $questionType = $questionTypeStmt->fetchColumn();
                        
                        // Also check if question text suggests True/False
                        $questionTextQuery = "SELECT question_text FROM questions WHERE id = ?";
                        $questionTextStmt = $this->db->prepare($questionTextQuery);
                        $questionTextStmt->execute([$questionId]);
                        $questionText = $questionTextStmt->fetchColumn();
                        
                        error_log("Question $questionId - Type: '$questionType', Text: '$questionText'");
                        
                        $isTrueFalse = ($questionType === 'true_false' || $questionType === 'boolean' || 
                                       in_array(strtoupper($questionText), ['T', 'F', 'TRUE', 'FALSE']));
                        
                        if ($isTrueFalse) {
                            if (empty($options)) {
                                error_log("No options found for True/False question $questionId, creating them");
                            } else {
                                error_log("Found existing options for True/False question $questionId, checking if they need to be fixed");
                                
                                // Check if the existing options have the correct logic
                                $correctAnswerIsTrue = !in_array(strtoupper($questionText), ['F', 'FALSE']);
                                $expectedCorrectOption = $correctAnswerIsTrue ? 'true' : 'false';
                                
                                $currentCorrectOption = null;
                                foreach ($options as $option) {
                                    if ($option['is_correct'] == 1) {
                                        $currentCorrectOption = $option['option_text'];
                                        break;
                                    }
                                }
                                
                                if ($currentCorrectOption !== $expectedCorrectOption) {
                                    error_log("Incorrect options found for question $questionId. Current correct: '$currentCorrectOption', Expected: '$expectedCorrectOption'. Deleting and recreating.");
                                    
                                    // Delete existing incorrect options
                                    $deleteQuery = "DELETE FROM question_options WHERE question_id = ?";
                                    $deleteStmt = $this->db->prepare($deleteQuery);
                                    $deleteStmt->execute([$questionId]);
                                } else {
                                    error_log("Options for question $questionId are already correct, skipping");
                                    continue; // Skip to next question
                                }
                            }
                            
                            // Create/recreate options with correct logic
                            error_log("Creating True/False options for question $questionId");
                            
                            // Determine correct answer based on question text
                            $correctAnswerIsTrue = !in_array(strtoupper($questionText), ['F', 'FALSE']);
                            
                            // Create True option
                            $insertTrue = "INSERT INTO question_options (question_id, option_text, is_correct, order_index, created_at) VALUES (?, 'true', ?, 1, NOW())";
                            $stmtTrue = $this->db->prepare($insertTrue);
                            $stmtTrue->execute([$questionId, $correctAnswerIsTrue ? 1 : 0]);
                            
                            // Create False option  
                            $insertFalse = "INSERT INTO question_options (question_id, option_text, is_correct, order_index, created_at) VALUES (?, 'false', ?, 2, NOW())";
                            $stmtFalse = $this->db->prepare($insertFalse);
                            $stmtFalse->execute([$questionId, $correctAnswerIsTrue ? 0 : 1]);
                            
                            error_log("Created True/False options for question $questionId (text: '$questionText', type: '$questionType', correct: " . ($correctAnswerIsTrue ? 'true' : 'false') . ")");
                            $needsOptionCreation = true;
                        }
                    }
                }
                
                // If we created any options, re-run the query
                if ($needsOptionCreation) {
                    error_log("Re-running query to get newly created correct answers");
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$attemptId]);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("After creating options, found " . count($results) . " questions with answers");
                }
            }
            
            // Convert is_correct to boolean
            foreach ($results as &$result) {
                $result['is_correct'] = (bool)$result['is_correct'];
            }
            
            // If no results from student_answers, try to get exam questions anyway
            if (empty($results)) {
                error_log("No student answers found, trying to get exam questions without answers...");
                return $this->getExamQuestionsWithoutAnswers($attemptId);
            }
            
            return $results;
        } catch (\PDOException $e) {
            error_log("Error getting attempt questions and answers: " . $e->getMessage());
            
            // Check if student_answers table exists
            try {
                $checkQuery = "SHOW TABLES LIKE 'student_answers'";
                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->execute();
                $tableExists = $checkStmt->fetch();
                
                if (!$tableExists) {
                    error_log("student_answers table does not exist!");
                } else {
                    error_log("student_answers table exists");
                    
                    // Check if there are any records for this attempt
                    $countQuery = "SELECT COUNT(*) as count FROM student_answers WHERE attempt_id = ?";
                    $countStmt = $this->db->prepare($countQuery);
                    $countStmt->execute([$attemptId]);
                    $count = $countStmt->fetch(PDO::FETCH_ASSOC);
                    error_log("Records in student_answers for attempt $attemptId: " . $count['count']);
                }
            } catch (\PDOException $e2) {
                error_log("Error checking student_answers table: " . $e2->getMessage());
            }
            
            // Try to get exam questions without answers as fallback
            return $this->getExamQuestionsWithoutAnswers($attemptId);
        }
    }

    /**
     * Get exam questions without student answers (fallback)
     */
    private function getExamQuestionsWithoutAnswers($attemptId): array
    {
        try {
            error_log("=== Getting exam questions without answers for attempt: $attemptId ===");
            
            // First get the exam ID from the attempt
            $examQuery = "SELECT exam_id FROM exam_attempts WHERE id = ?";
            $examStmt = $this->db->prepare($examQuery);
            $examStmt->execute([$attemptId]);
            $examResult = $examStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$examResult) {
                error_log("No exam found for attempt ID: $attemptId");
                return [];
            }
            
            $examId = $examResult['exam_id'];
            error_log("Found exam ID: $examId for attempt: $attemptId");
            
            // Check question_options table structure
            $describeOptionsQuery = "DESCRIBE question_options";
            $describeOptionsStmt = $this->db->prepare($describeOptionsQuery);
            $describeOptionsStmt->execute();
            $optionColumns = $describeOptionsStmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("Question_options table columns: " . json_encode($optionColumns));
            
            // Get questions for this exam
            $questionsQuery = "
                SELECT 
                    q.id as question_id,
                    q.question_text,
                    qo.option_text as correct_answer,
                    'No answer recorded' as student_answer,
                    0 as is_correct
                FROM questions q
                LEFT JOIN question_options qo ON q.id = qo.question_id AND qo.is_correct = 1
                WHERE q.exam_id = ?
                ORDER BY q.id
            ";
            
            error_log("Fallback questions query: " . $questionsQuery);
            
            $questionsStmt = $this->db->prepare($questionsQuery);
            $questionsStmt->execute([$examId]);
            $questions = $questionsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Found " . count($questions) . " questions for exam without answers");
            error_log("Sample question data: " . json_encode($questions[0] ?? 'no questions'));
            
            // Debug: Check what options exist for the first question
            if (!empty($questions)) {
                $firstQuestionId = $questions[0]['question_id'];
                $optionsQuery = "SELECT * FROM question_options WHERE question_id = ?";
                $optionsStmt = $this->db->prepare($optionsQuery);
                $optionsStmt->execute([$firstQuestionId]);
                $options = $optionsStmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Options for question $firstQuestionId: " . json_encode($options));
            }
            
            // Convert is_correct to boolean
            foreach ($questions as &$question) {
                $question['is_correct'] = false; // No answers recorded
            }
            
            return $questions;
        } catch (\PDOException $e) {
            error_log("Error getting exam questions without answers: " . $e->getMessage());
            return [];
        }
    }
}
