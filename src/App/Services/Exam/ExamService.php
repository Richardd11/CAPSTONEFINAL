<?php

namespace App\Services\Exam;

use App\DAO\Exam\ExamDAO;
use App\DAO\Exam\ExamAttemptDAO;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\DAO\Question\QuestionDAO;
use App\DAO\QuestionOption\QuestionOptionDAO;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Interfaces\ExamServiceInterface;

class ExamService implements ExamServiceInterface
{
    private $examDAO;
    private $examAttemptDAO;
    private $questionDAO;
    private $questionOptionDAO;

    public function __construct(
        ExamDAO $examDAO = null,
        QuestionDAO $questionDAO = null,
        QuestionOptionDAO $questionOptionDAO = null
    ) {
        $this->examDAO = $examDAO ?? new ExamDAO();
        $this->examAttemptDAO = new ExamAttemptDAO();
        $this->questionDAO = $questionDAO ?? new QuestionDAO();
        $this->questionOptionDAO = $questionOptionDAO ?? new QuestionOptionDAO();
    }

    /**
     * Create a new exam with questions
     */
    public function createExam($data)
    {
        try {
            error_log("CREATE EXAM - Creating new exam with data: " . json_encode($data));
            
            // Create exam model
            $exam = new Exam($data);
            
            // Validate exam data
            $errors = $exam->validate();
            if (!empty($errors)) {
                error_log("CREATE EXAM - Validation errors: " . json_encode($errors));
                error_log("CREATE EXAM - Exam data that failed validation: " . json_encode($data));
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors),
                    'validation_errors' => $errors
                ];
            }

            // Create exam
            $createdExam = $this->examDAO->create($exam);
            if (!$createdExam) {
                return [
                    'success' => false,
                    'message' => 'Failed to create exam.'
                ];
            }

            $examId = $createdExam->getId();
            $totalPoints = 0;

            // Create questions
            if (!empty($data['questions'])) {
                foreach ($data['questions'] as $questionData) {
                    $questionData['exam_id'] = $examId;
                    $question = new Question($questionData);
                    
                    // Validate question
                    $questionErrors = $question->validate();
                    if (!empty($questionErrors)) {
                        // If question validation fails, delete the exam and return error
                        $this->examDAO->delete($examId);
                        return [
                            'success' => false,
                            'message' => 'Question validation failed: ' . implode(', ', $questionErrors)
                        ];
                    }

                    $createdQuestion = $this->questionDAO->create($question);
                    if (!$createdQuestion) {
                        // If question creation fails, delete the exam and return error
                        $this->examDAO->delete($examId);
                        return [
                            'success' => false,
                            'message' => 'Failed to create question.'
                        ];
                    }

                    $questionId = $createdQuestion->getId();
                    $totalPoints += $question->getPoints();

                    // Create options for multiple choice questions
                    if ($question->getQuestionType() === 'multiple_choice' && !empty($questionData['options'])) {
                        foreach ($questionData['options'] as $optionData) {
                            $optionData['question_id'] = $questionId;
                            $option = new QuestionOption($optionData);
                            
                            $optionErrors = $option->validate();
                            if (!empty($optionErrors)) {
                                // If option validation fails, delete the exam and return error
                                $this->examDAO->delete($examId);
                                return [
                                    'success' => false,
                                    'message' => 'Option validation failed: ' . implode(', ', $optionErrors)
                                ];
                            }

                            $this->questionOptionDAO->create($option);
                        }
                    }
                    
                    // Create options for True/False questions
                    if ($question->getQuestionType() === 'true_false') {
                        // Determine which is the correct answer
                        $correctAnswer = $questionData['correct_answer'] ?? 'true';
                        
                        // Create True option
                        $trueOption = new QuestionOption([
                            'question_id' => $questionId,
                            'option_text' => 'true',
                            'is_correct' => ($correctAnswer === 'true' ? 1 : 0),
                            'order_index' => 0
                        ]);
                        $this->questionOptionDAO->create($trueOption);
                        
                        // Create False option
                        $falseOption = new QuestionOption([
                            'question_id' => $questionId,
                            'option_text' => 'false',
                            'is_correct' => ($correctAnswer === 'false' ? 1 : 0),
                            'order_index' => 1
                        ]);
                        $this->questionOptionDAO->create($falseOption);
                        
                        error_log("Created True/False options for question $questionId with correct answer: $correctAnswer");
                    }
                }
            }

            // Update exam with total points
            $createdExam->setTotalPoints($totalPoints);
            $this->examDAO->update($createdExam);

            return [
                'success' => true,
                'message' => 'Exam created successfully.',
                'data' => $createdExam->toArray()
            ];

        } catch (\Exception $e) {
            error_log("Error creating exam: " . $e->getMessage());
            error_log("Error creating exam - Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'An error occurred while creating the exam: ' . $e->getMessage(),
                'error_details' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Update an existing exam
     */
    public function updateExam($examId, $data)
    {
        try {
            // Get existing exam
            $existingExam = $this->examDAO->getById($examId);
            if (!$existingExam) {
                return [
                    'success' => false,
                    'message' => 'Exam not found.'
                ];
            }

            // CRITICAL FIX: Preserve assignment data to prevent exam moving to wrong year
            $existingData = $existingExam->toArray();
            
            // Preserve critical assignment fields that should NEVER change during update
            $preservedFields = [
                'year_level' => $existingData['year_level'],
                'section' => $existingData['section'], 
                'academic_year' => $existingData['academic_year'],
                'semester' => $existingData['semester'],
                'faculty_id' => $existingData['faculty_id'],
                'id' => $examId
            ];
            
            // CRITICAL FIX: Remove assignment fields from incoming data to prevent override
            unset($data['year_level'], $data['section'], $data['academic_year'], $data['semester'], $data['faculty_id'], $data['id']);
            
            // Merge data but preserve critical fields
            $mergedData = array_merge($existingData, $data, $preservedFields);
            
            error_log("UPDATE EXAM - Original data: " . json_encode($existingData));
            error_log("UPDATE EXAM - Incoming data: " . json_encode($data));
            error_log("UPDATE EXAM - Preserving assignment data: " . json_encode($preservedFields));
            error_log("UPDATE EXAM - Final merged data: " . json_encode($mergedData));
            
            $exam = new Exam($mergedData);
            $exam->setId($examId);

            // Validate exam data
            $errors = $exam->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors)
                ];
            }

            // Delete existing questions and options
            $this->questionDAO->deleteByExamId($examId);

            $totalPoints = 0;

            // Create new questions
            if (!empty($data['questions'])) {
                foreach ($data['questions'] as $questionData) {
                    $questionData['exam_id'] = $examId;
                    $question = new Question($questionData);
                    
                    $questionErrors = $question->validate();
                    if (!empty($questionErrors)) {
                        return [
                            'success' => false,
                            'message' => 'Question validation failed: ' . implode(', ', $questionErrors)
                        ];
                    }

                    $createdQuestion = $this->questionDAO->create($question);
                    if (!$createdQuestion) {
                        return [
                            'success' => false,
                            'message' => 'Failed to create question.'
                        ];
                    }

                    $questionId = $createdQuestion->getId();
                    $totalPoints += $question->getPoints();

                    // Create options for multiple choice questions
                    if ($question->getQuestionType() === 'multiple_choice' && !empty($questionData['options'])) {
                        foreach ($questionData['options'] as $optionData) {
                            $optionData['question_id'] = $questionId;
                            $option = new QuestionOption($optionData);
                            $this->questionOptionDAO->create($option);
                        }
                    }
                    
                    // Create options for True/False questions
                    if ($question->getQuestionType() === 'true_false') {
                        // Determine which is the correct answer
                        $correctAnswer = $questionData['correct_answer'] ?? 'true';
                        
                        // Create True option
                        $trueOption = new QuestionOption([
                            'question_id' => $questionId,
                            'option_text' => 'true',
                            'is_correct' => ($correctAnswer === 'true' ? 1 : 0),
                            'order_index' => 0
                        ]);
                        $this->questionOptionDAO->create($trueOption);
                        
                        // Create False option
                        $falseOption = new QuestionOption([
                            'question_id' => $questionId,
                            'option_text' => 'false',
                            'is_correct' => ($correctAnswer === 'false' ? 1 : 0),
                            'order_index' => 1
                        ]);
                        $this->questionOptionDAO->create($falseOption);
                        
                        error_log("Created True/False options for question $questionId with correct answer: $correctAnswer");
                    }
                }
            }

            // Update exam with total points
            $exam->setTotalPoints($totalPoints);
            
            error_log("UPDATE EXAM SERVICE - About to update exam with data: " . json_encode($exam->toArray()));
            
            $result = $this->examDAO->update($exam);
            
            error_log("UPDATE EXAM SERVICE - DAO update result: " . ($result ? 'SUCCESS' : 'FAILED'));

            if ($result) {
                $finalExamData = $this->examDAO->getById($examId);
                error_log("UPDATE EXAM SERVICE - Final exam data after update: " . json_encode($finalExamData ? $finalExamData->toArray() : 'NULL'));
                
                return [
                    'success' => true,
                    'message' => 'Exam updated successfully.',
                    'exam_id' => $examId,
                    'data' => $exam->toArray()
                ];
            }

            error_log("UPDATE EXAM SERVICE - Failed to update exam in database");
            return [
                'success' => false,
                'message' => 'Failed to update exam.'
            ];

        } catch (\Exception $e) {
            error_log("Error updating exam: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while updating the exam.'
            ];
        }
    }

    /**
     * Delete an exam
     */
    public function deleteExam($examId)
    {
        try {
            $exam = $this->examDAO->getById($examId);
            if (!$exam) {
                return [
                    'success' => false,
                    'message' => 'Exam not found.'
                ];
            }

            // Delete questions and options (cascade)
            $this->questionDAO->deleteByExamId($examId);

            // Delete exam
            $result = $this->examDAO->delete($examId);
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Exam deleted successfully.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to delete exam.'
            ];

        } catch (\Exception $e) {
            error_log("Error deleting exam: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while deleting the exam.'
            ];
        }
    }

    /**
     * Get exam by ID
     */
    public function getExamById($examId)
    {
        return $this->examDAO->getById($examId);
    }

    /**
     * Get exams by faculty
     */
    public function getExamsByFaculty($facultyId)
    {
        return $this->examDAO->getByFaculty($facultyId);
    }

    /**
     * Get exam questions
     */
    public function getExamQuestions($examId)
    {
        return $this->questionDAO->getByExamId($examId);
    }

    /**
     * Get question options
     */
    public function getQuestionOptions($questionId)
    {
        return $this->questionOptionDAO->getByQuestionId($questionId);
    }

    /**
     * Get exams by filters
     */
    public function getExamsByFilters($filters = [])
    {
        return $this->examDAO->getByFilters($filters);
    }

    /**
     * Get comprehensive exam statistics for a faculty member
     */
    public function getExamStats($facultyId = null): array
    {
        try {
            $exams = $this->examDAO->getByFilters(['faculty_id' => $facultyId]);
            $stats = $this->calculateExamStatistics($exams);
            
            return [
                'total_exams' => $stats['total'],
                'active_exams' => $stats['active'],
                'completed_exams' => $stats['completed'],
                'scheduled_exams' => $stats['scheduled'],
                'average_score' => $stats['avg_score'],
                'highest_score' => $stats['max_score'],
                'lowest_score' => $stats['min_score'],
                'total_students' => $stats['student_count'],
                'score_distribution' => $stats['distribution']
            ];
        } catch (\Exception $e) {
            error_log("Error getting exam stats: " . $e->getMessage());
            return $this->getEmptyStats();
        }
    }

    /**
     * Calculate comprehensive statistics from exams
     */
    private function calculateExamStatistics(array $exams): array
    {
        $stats = [
            'total' => count($exams),
            'active' => 0,
            'completed' => 0,
            'scheduled' => 0,
            'scores' => [],
            'student_count' => 0
        ];
        
        foreach ($exams as $exam) {
            $this->updateExamStatusCounts($exam, $stats);
            $this->collectExamScores($exam, $stats);
        }
        
        return $this->finalizeStatistics($stats);
    }

    /**
     * Update exam status counts
     */
    private function updateExamStatusCounts($exam, array &$stats): void
    {
        $status = $exam->getStatus();
        switch ($status) {
            case 'active':
            case 'published':
                $stats['active']++;
                break;
            case 'completed':
            case 'ended':
                $stats['completed']++;
                break;
            case 'scheduled':
                $stats['scheduled']++;
                break;
        }
    }

    /**
     * Collect scores from exam results
     */
    private function collectExamScores($exam, array &$stats): void
    {
        try {
            $examResults = $this->getExamResults($exam->getId());
            foreach ($examResults as $result) {
                if (isset($result['score']) && is_numeric($result['score'])) {
                    $stats['scores'][] = (float)$result['score'];
                    $stats['student_count']++;
                }
            }
        } catch (\Exception $e) {
            // Continue if exam results not available
        }
    }

    /**
     * Finalize statistics calculations
     */
    private function finalizeStatistics(array $stats): array
    {
        $scores = $stats['scores'];
        $count = count($scores);
        
        if ($count > 0) {
            $stats['avg_score'] = round(array_sum($scores) / $count, 1);
            $stats['max_score'] = round(max($scores), 1);
            $stats['min_score'] = round(min($scores), 1);
            $stats['distribution'] = $this->calculateScoreDistribution($scores);
        } else {
            $stats['avg_score'] = 0;
            $stats['max_score'] = 0;
            $stats['min_score'] = 0;
            $stats['distribution'] = ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0];
        }
        
        return $stats;
    }

    /**
     * Calculate score distribution
     */
    private function calculateScoreDistribution(array $scores): array
    {
        $distribution = ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0];
        
        foreach ($scores as $score) {
            if ($score >= 90) $distribution['excellent']++;
            elseif ($score >= 80) $distribution['good']++;
            elseif ($score >= 70) $distribution['fair']++;
            else $distribution['poor']++;
        }
        
        return $distribution;
    }

    /**
     * Get empty statistics structure
     */
    private function getEmptyStats(): array
    {
        return [
            'total_exams' => 0,
            'active_exams' => 0,
            'completed_exams' => 0,
            'scheduled_exams' => 0,
            'average_score' => 0,
            'highest_score' => 0,
            'lowest_score' => 0,
            'total_students' => 0,
            'score_distribution' => ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0]
        ];
    }
    
    /**
     * Get exam results for a specific exam
     * This is a placeholder - you would implement actual database queries
     */
    private function getExamResults($examId): array
    {
        try {
            // This would typically query student_exam_results table
            // For now, return empty array since we don't have actual student results yet
            // In a real implementation, this would be:
            // SELECT score FROM student_exam_results WHERE exam_id = ?
            return []; // Return empty array for real data - no fake results
        } catch (\Exception $e) {
            error_log("Error getting exam results: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed exam results with student information
     */
    public function getDetailedExamResults($examId, $studentId = null): array
    {
        try {
            error_log("=== GETTING DETAILED EXAM RESULTS ===");
            error_log("Exam ID: $examId, Student ID filter: " . ($studentId ?? 'all'));
            
            // Get exam attempts with comprehensive student data
            $db = \App\Config\Database::getInstance()->getConnection();
            
            $query = "
                SELECT 
                    ea.id as attempt_id,
                    ea.student_id,
                    ea.score as stored_score,
                    ea.status,
                    ea.start_time,
                    ea.end_time,
                    ea.created_at as completed_at,
                    u.full_name as name,
                    u.school_id as student_school_id,
                    e.title as exam_title
                FROM exam_attempts ea
                INNER JOIN users u ON ea.student_id = u.user_id AND u.role = 'student'
                INNER JOIN exams e ON ea.exam_id = e.id
                WHERE ea.exam_id = ?
            ";
            
            $params = [$examId];
            if ($studentId) {
                $query .= " AND ea.student_id = ?";
                $params[] = $studentId;
            }
            
            $query .= " ORDER BY ea.score DESC, u.full_name ASC";
            
            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $attempts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            error_log("Found " . count($attempts) . " exam attempts");
            
            // Format the results with accurate scoring
            $formattedResults = [];
            foreach ($attempts as $attempt) {
                // Get comprehensive details for accurate scoring
                $comprehensiveDetails = $this->getComprehensiveStudentExamDetails($attempt['attempt_id']);
                
                if ($comprehensiveDetails) {
                    // Use recalculated accurate score
                    $accurateScore = $comprehensiveDetails['score'];
                    $correctAnswers = $comprehensiveDetails['correct_answers'];
                    $totalQuestions = $comprehensiveDetails['total_questions'];
                    
                    error_log("Student: {$attempt['name']} - Stored: {$attempt['stored_score']}% - Accurate: {$accurateScore}%");
                } else {
                    // Fallback to stored score if comprehensive details fail
                    $accurateScore = (float)$attempt['stored_score'];
                    $correctAnswers = 0;
                    $totalQuestions = 0;
                    
                    error_log("Using fallback score for {$attempt['name']}: {$accurateScore}%");
                }
                
                // Calculate grade based on accurate score
                $grade = $this->calculateGrade($accurateScore);
                $status = $this->getPerformanceStatus($accurateScore);
                
                $formattedResults[] = [
                    'id' => (int)$attempt['attempt_id'],
                    'name' => $attempt['name'],
                    'student_id' => $attempt['student_school_id'],
                    'score' => round($accurateScore, 2),
                    'grade' => $grade,
                    'status' => $status,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions,
                    'completed_at' => $attempt['end_time'] ?: $attempt['completed_at'],
                    'exam_status' => $attempt['status']
                ];
            }
            
            error_log("Returning " . count($formattedResults) . " formatted results");
            return $formattedResults;
            
        } catch (\Exception $e) {
            error_log("Error getting detailed exam results: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get count of students who completed an exam
     */
    public function getExamStudentCount($examId): int
    {
        try {
            return $this->examAttemptDAO->getExamStudentCount($examId);
        } catch (\Exception $e) {
            error_log("Error getting exam student count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate letter grade based on score percentage
     */
    private function calculateGrade($score): string
    {
        if ($score >= 97) return 'A+';
        if ($score >= 93) return 'A';
        if ($score >= 90) return 'A-';
        if ($score >= 87) return 'B+';
        if ($score >= 83) return 'B';
        if ($score >= 80) return 'B-';
        if ($score >= 77) return 'C+';
        if ($score >= 73) return 'C';
        if ($score >= 70) return 'C-';
        if ($score >= 67) return 'D+';
        if ($score >= 65) return 'D';
        return 'F';
    }

    /**
     * Get performance status based on score
     */
    private function getPerformanceStatus($score): string
    {
        if ($score >= 90) return 'Outstanding';
        if ($score >= 85) return 'Excellent';
        if ($score >= 80) return 'Very Good';
        if ($score >= 75) return 'Satisfactory';
        if ($score >= 60) return 'Needs Improvement';
        return 'Unsatisfactory';
    }

    /**
     * Get detailed exam attempt information including questions and answers
     */
    public function getExamAttemptDetails($attemptId): ?array
    {
        try {
            error_log("=== EXAM SERVICE: Getting attempt details for ID: $attemptId ===");
            
            // Get the exam attempt details
            $attempt = $this->examAttemptDAO->getAttemptById($attemptId);
            if (!$attempt) {
                error_log("No attempt found for ID: $attemptId");
                return null;
            }
            
            error_log("Found attempt: " . json_encode($attempt));

            // Get the questions and student answers for this attempt
            $questionsWithAnswers = $this->examAttemptDAO->getAttemptQuestionsAndAnswers($attemptId);
            error_log("Questions with answers: " . json_encode($questionsWithAnswers));
            
            // Calculate correct/incorrect counts
            $correctAnswers = 0;
            $totalQuestions = count($questionsWithAnswers);
            
            foreach ($questionsWithAnswers as &$question) {
                if ($question['is_correct']) {
                    $correctAnswers++;
                }
            }
            
            $incorrectAnswers = $totalQuestions - $correctAnswers;
            
            return [
                'attempt_id' => $attemptId,
                'score' => $attempt['score'],
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'incorrect_answers' => $incorrectAnswers,
                'questions' => $questionsWithAnswers
            ];
        } catch (\Exception $e) {
            error_log("Error getting exam attempt details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get score analytics for an exam
     */
    public function getExamAnalytics($examId): array
    {
        try {
            $results = $this->getExamResults($examId);
            if (empty($results)) {
                return $this->getEmptyAnalytics();
            }

            $scores = array_column($results, 'score');
            return [
                'total_attempts' => count($scores),
                'average_score' => round(array_sum($scores) / count($scores), 1),
                'median_score' => $this->calculateMedian($scores),
                'highest_score' => max($scores),
                'lowest_score' => min($scores),
                'standard_deviation' => $this->calculateStandardDeviation($scores),
                'score_distribution' => $this->calculateScoreDistribution($scores),
                'grade_distribution' => $this->calculateGradeDistribution($scores),
                'pass_rate' => $this->calculatePassRate($scores),
                'score_ranges' => $this->calculateScoreRanges($scores)
            ];
        } catch (\Exception $e) {
            error_log("Error getting exam analytics: " . $e->getMessage());
            return $this->getEmptyAnalytics();
        }
    }

    /**
     * Calculate median score
     */
    private function calculateMedian(array $scores): float
    {
        sort($scores);
        $count = count($scores);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return ($scores[$middle - 1] + $scores[$middle]) / 2;
        } else {
            return $scores[$middle];
        }
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation(array $scores): float
    {
        $mean = array_sum($scores) / count($scores);
        $variance = array_sum(array_map(function($score) use ($mean) {
            return pow($score - $mean, 2);
        }, $scores)) / count($scores);
        
        return round(sqrt($variance), 2);
    }

    /**
     * Calculate grade distribution
     */
    private function calculateGradeDistribution(array $scores): array
    {
        $distribution = ['A+' => 0, 'A' => 0, 'B+' => 0, 'B' => 0, 'C+' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        
        foreach ($scores as $score) {
            $grade = $this->getLetterGrade($score);
            $distribution[$grade]++;
        }
        
        return $distribution;
    }

    /**
     * Calculate pass rate
     */
    private function calculatePassRate(array $scores): float
    {
        $passCount = count(array_filter($scores, function($score) {
            return $score >= 60; // Assuming 60 is passing grade
        }));
        
        return round(($passCount / count($scores)) * 100, 1);
    }

    /**
     * Calculate score ranges
     */
    private function calculateScoreRanges(array $scores): array
    {
        $ranges = [
            '90-100' => 0,
            '80-89' => 0,
            '70-79' => 0,
            '60-69' => 0,
            '50-59' => 0,
            '0-49' => 0
        ];
        
        foreach ($scores as $score) {
            if ($score >= 90) $ranges['90-100']++;
            elseif ($score >= 80) $ranges['80-89']++;
            elseif ($score >= 70) $ranges['70-79']++;
            elseif ($score >= 60) $ranges['60-69']++;
            elseif ($score >= 50) $ranges['50-59']++;
            else $ranges['0-49']++;
        }
        
        return $ranges;
    }

    /**
     * Get letter grade for score
     */
    private function getLetterGrade(float $score): string
    {
        if ($score >= 97) return 'A+';
        if ($score >= 93) return 'A';
        if ($score >= 90) return 'A-';
        if ($score >= 87) return 'B+';
        if ($score >= 83) return 'B';
        if ($score >= 80) return 'B-';
        if ($score >= 77) return 'C+';
        if ($score >= 73) return 'C';
        if ($score >= 70) return 'C-';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * Get empty analytics structure
     */
    private function getEmptyAnalytics(): array
    {
        return [
            'total_attempts' => 0,
            'average_score' => 0,
            'median_score' => 0,
            'highest_score' => 0,
            'lowest_score' => 0,
            'standard_deviation' => 0,
            'score_distribution' => ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0],
            'grade_distribution' => ['A+' => 0, 'A' => 0, 'B+' => 0, 'B' => 0, 'C+' => 0, 'C' => 0, 'D' => 0, 'F' => 0],
            'pass_rate' => 0,
            'score_ranges' => ['90-100' => 0, '80-89' => 0, '70-79' => 0, '60-69' => 0, '50-59' => 0, '0-49' => 0]
        ];
    }

    // NOTE: Mock data methods removed to prevent fake data from appearing
    // These methods were generating fake students and scores
    // Real database queries should be implemented instead

    /**
     * Auto-save exam as draft
     */
    public function autoSaveExam($data)
    {
        try {
            // Mark as draft
            $data['is_draft'] = true;
            $data['status'] = 'draft';
            
            // If exam exists, update it
            if (isset($data['exam_id']) && $data['exam_id']) {
                return $this->updateExam($data['exam_id'], $data);
            } else {
                // Create new draft
                return $this->createExam($data);
            }
        } catch (\Exception $e) {
            error_log("Error auto-saving exam: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate exam data
     */
    public function validateExam($data)
    {
        try {
            $errors = [];
            $warnings = [];

            // Validate exam metadata
            if (empty($data['title'])) {
                $errors[] = 'Exam title is required';
            }

            if (empty($data['subject_id'])) {
                $errors[] = 'Subject is required';
            }

            if (empty($data['questions']) || count($data['questions']) === 0) {
                $errors[] = 'Exam must have at least one question';
            }

            // Validate each question
            if (!empty($data['questions'])) {
                foreach ($data['questions'] as $index => $question) {
                    $questionNum = $index + 1;
                    
                    if (empty($question['question_text'])) {
                        $errors[] = "Question {$questionNum}: Question text is required";
                    }

                    if (!isset($question['points']) || $question['points'] < 0) {
                        $errors[] = "Question {$questionNum}: Points must be greater than 0";
                    }

                    // Type-specific validation
                    switch ($question['question_type']) {
                        case 'multiple_choice':
                            if (empty($question['options']) || count($question['options']) < 2) {
                                $errors[] = "Question {$questionNum}: Multiple choice questions must have at least 2 options";
                            }
                            $hasCorrect = false;
                            foreach ($question['options'] as $option) {
                                if ($option['is_correct']) {
                                    $hasCorrect = true;
                                    break;
                                }
                            }
                            if (!$hasCorrect) {
                                $errors[] = "Question {$questionNum}: Must have a correct answer selected";
                            }
                            break;

                        case 'true_false':
                            if (!isset($question['correct_answer'])) {
                                $errors[] = "Question {$questionNum}: Must have a correct answer";
                            }
                            break;

                        case 'enumeration':
                            if (empty($question['correct_answer'])) {
                                $errors[] = "Question {$questionNum}: Must have correct answers";
                            }
                            break;

                        case 'essay':
                            if (empty($question['rubric'])) {
                                $warnings[] = "Question {$questionNum}: Essay questions should have a rubric for better grading";
                            }
                            break;
                    }
                }
            }

            // Check time limit
            if (isset($data['time_limit'])) {
                $estimatedTime = count($data['questions']) * 2; // 2 minutes per question
                if ($data['time_limit'] < $estimatedTime) {
                    $warnings[] = "Time limit may be too short. Estimated time needed: {$estimatedTime} minutes";
                }
            }

            return [
                'success' => true,
                'valid' => count($errors) === 0,
                'errors' => $errors,
                'warnings' => $warnings
            ];
        } catch (\Exception $e) {
            error_log("Error validating exam: " . $e->getMessage());
            return [
                'success' => false,
                'valid' => false,
                'errors' => ['Validation failed: ' . $e->getMessage()],
                'warnings' => []
            ];
        }
    }

    /**
     * Get exams available for a student based on year level and section
     */
    public function getExamsForStudent($yearLevel, $section): array
    {
        try {
            // Get all active exams
            $allExams = $this->examDAO->getAll();
            $availableExams = [];
            
            // Debug: Log all exams
            error_log("Total exams in database: " . count($allExams));
            error_log("Looking for exams with Year Level: " . $yearLevel . ", Section: " . $section);
            
            foreach ($allExams as $exam) {
                // Debug: Log each exam details
                error_log("Exam: " . $exam->getTitle() . " - Year: " . $exam->getYearLevel() . ", Section: " . $exam->getSection() . ", Active: " . ($exam->getIsActive() ? 'Yes' : 'No'));
                
                // Check if exam is active and matches student's year level and section
                $examYearLevel = $exam->getYearLevel();
                $examSection = $exam->getSection();
                
                // Normalize year level comparison (handle "1" vs "1st Year" formats)
                $yearLevelMatch = $this->compareYearLevels($yearLevel, $examYearLevel);
                
                error_log("Checking exam: " . $exam->getTitle() . " - Active: " . ($exam->getIsActive() ? 'Yes' : 'No') . ", YearMatch: " . ($yearLevelMatch ? 'Yes' : 'No') . ", SectionMatch: " . ($examSection === $section ? 'Yes' : 'No'));
                
                if ($exam->getIsActive() && 
                    $yearLevelMatch && 
                    $examSection === $section) {
                    
                    error_log("Exam passed initial filters, checking date range...");
                    
                    // Check if student has already completed this exam
                    $authService = new \App\Services\Auth\AuthService();
                    $currentUser = $authService->getCurrentUserModel();
                    if ($currentUser) {
                        $existingAttempt = $this->examAttemptDAO->getAttemptByStudentAndExam($currentUser->getUserId(), $exam->getId());
                        if ($existingAttempt && $existingAttempt['status'] === 'completed') {
                            error_log("Exam '" . $exam->getTitle() . "' already completed by student");
                            continue; // Skip this exam
                        }
                    }
                    
                    // Also check if exam is within the date range
                    $currentDate = date('Y-m-d H:i:s');
                    $startDate = $exam->getStartDate();
                    $endDate = $exam->getEndDate();
                    
                    // Check if exam is within the date range (with correct timezone)
                    $isWithinDateRange = true;
                    
                    // Log date information for debugging
                    error_log("Date check for '" . $exam->getTitle() . "': Current=$currentDate, Start=" . ($startDate ?: 'NULL') . ", End=" . ($endDate ?: 'NULL'));
                    
                    if ($startDate && $currentDate < $startDate) {
                        $isWithinDateRange = false; // Exam hasn't started yet
                        error_log("Exam hasn't started yet - Current: $currentDate < Start: $startDate");
                    }
                    
                    if ($endDate && $currentDate > $endDate) {
                        $isWithinDateRange = false; // Exam has ended
                        error_log("Exam has ended - Current: $currentDate > End: $endDate");
                    }
                    
                    if ($isWithinDateRange) {
                        error_log("✅ Exam '" . $exam->getTitle() . "' added to available exams!");
                        $examArray = $exam->toArray();
                        
                        // Add additional computed fields
                        $examArray['duration_minutes'] = $exam->getTimeLimit();
                        $examArray['total_questions'] = count($exam->getQuestions());
                        
                        $availableExams[] = $examArray;
                    } else {
                        error_log("❌ Exam '" . $exam->getTitle() . "' failed date range check");
                    }
                }
            }
            
            return $availableExams;
        } catch (\Exception $e) {
            error_log("Error getting exams for student: " . $e->getMessage());
            return [];
        }
    }

/**
 * Get student's exam history
 */
    public function getStudentExamHistory($studentId): array
    {
        try {
            return $this->examAttemptDAO->getStudentExamHistory($studentId);
        } catch (\Exception $e) {
            error_log("Error getting student exam history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if student is eligible for an exam
     */
    public function isStudentEligibleForExam($studentId, $examId): bool
    {
        try {
            // Get exam details
            $exam = $this->examDAO->getById($examId);
            if (!$exam) {
                return false;
            }
            
            // Get student details (you'll need to inject UserDAO or get from session)
            // For now, we'll get from the current user session
            $authService = new \App\Services\Auth\AuthService();
            $currentUser = $authService->getCurrentUserModel();
            
            if (!$currentUser || $currentUser->getUserId() != $studentId) {
                return false;
            }
            
            // Check if student's year level and section match exam requirements
            $studentYearLevel = $currentUser->getYearLevel();
            $studentSection = $currentUser->getSection();
            
            // Use normalized year level comparison
            $yearLevelMatch = $this->compareYearLevels($studentYearLevel, $exam->getYearLevel());
            
            if (!$yearLevelMatch || $exam->getSection() !== $studentSection) {
                return false;
            }
            
            // Check if exam is active
            if (!$exam->getIsActive()) {
                return false;
            }
            
            // Check date range
            $currentDate = date('Y-m-d H:i:s');
            $startDate = $exam->getStartDate();
            $endDate = $exam->getEndDate();
            
            if ($startDate && $currentDate < $startDate) {
                return false; // Exam hasn't started yet
            }
            
            if ($endDate && $currentDate > $endDate) {
                return false; // Exam has ended
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error checking student eligibility: " . $e->getMessage());
            return false;
        }
    }

/**
 * Get student's exam attempt
 */
public function getStudentExamAttempt($studentId, $examId): ?array
{
    try {
        return $this->examAttemptDAO->getAttemptByStudentAndExam($studentId, $examId);
    } catch (\Exception $e) {
        error_log("Error getting student exam attempt: " . $e->getMessage());
        return null;
    }
}

/**
 * Create or get exam attempt
 */
public function createOrGetExamAttempt($studentId, $examId): int
{
    try {
        // Check if student already has an attempt for this exam
        $existingAttempt = $this->examAttemptDAO->getAttemptByStudentAndExam($studentId, $examId);
        
        if ($existingAttempt && $existingAttempt['status'] !== 'completed') {
            return $existingAttempt['id'];
        }
        
        // Create new attempt if none exists or previous was completed
        return $this->examAttemptDAO->createAttempt($studentId, $examId);
    } catch (\Exception $e) {
        error_log("Error creating exam attempt: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Get student answers for an attempt
 */
public function getStudentAnswers($attemptId): array
{
    try {
        // Query student_answers table
        // For now, return empty array
        return [];
    } catch (\Exception $e) {
        error_log("Error getting student answers: " . $e->getMessage());
        return [];
    }
}

/**
 * Submit exam answers and calculate score
 */
public function submitExamAnswers($attemptId, $answers, $studentId): array
{
    try {
        error_log("=== SUBMITTING EXAM ANSWERS ===");
        error_log("Attempt ID: $attemptId");
        error_log("Student ID: $studentId");
        error_log("Answers received: " . json_encode($answers));
        
        // Process answers and handle AI grading for essays
        $this->processAnswersWithAI($attemptId, $answers);
        
        // Calculate score based on answers (including AI-graded essays)
        $scoreData = $this->calculateScore($answers, $attemptId);
        error_log("Calculated score data: " . json_encode($scoreData));
        
        // Extract percentage for database storage (backward compatibility)
        $scorePercentage = is_array($scoreData) ? $scoreData['percentage'] : $scoreData;
        
        // Mark exam attempt as completed
        $completed = $this->examAttemptDAO->completeAttempt($attemptId, $scorePercentage, $answers);
        error_log("Attempt completion result: " . ($completed ? 'success' : 'failed'));
        
        if ($completed) {
            return [
                'success' => true,
                'score' => $scorePercentage, // Keep for backward compatibility
                'score_data' => $scoreData, // New detailed score data
                'message' => 'Exam submitted successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save exam completion'
            ];
        }
    } catch (\Exception $e) {
        error_log("Error submitting exam answers: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to submit exam'
        ];
    }
}

/**
 * Save individual student answer
 */
public function saveStudentAnswer($attemptId, $questionId, $answer): bool
{
    try {
        error_log("=== SAVING INDIVIDUAL ANSWER ===");
        error_log("Attempt ID: $attemptId, Question ID: $questionId, Answer: $answer");
        
        // Save answer to database using DAO
        $result = $this->examAttemptDAO->saveAnswer($attemptId, $questionId, $answer);
        error_log("Individual save result: " . ($result ? 'success' : 'failed'));
        
        return $result;
    } catch (\Exception $e) {
        error_log("Error saving student answer: " . $e->getMessage());
        return false;
    }
}

    /**
     * Recalculate and update exam attempt score
     */
    public function recalculateExamScore($attemptId): array
    {
        try {
            error_log("=== RECALCULATING SCORE FOR ATTEMPT $attemptId ===");
            
            // Get attempt details with questions and answers
            $attemptDetails = $this->getExamAttemptDetails($attemptId);
            
            if (!$attemptDetails || empty($attemptDetails['questions'])) {
                return [
                    'success' => false,
                    'message' => 'No attempt details found'
                ];
            }
            
            $totalQuestions = count($attemptDetails['questions']);
            $correctAnswers = 0;
            
            // Count correct answers
            foreach ($attemptDetails['questions'] as $question) {
                if ($question['is_correct']) {
                    $correctAnswers++;
                }
            }
            
            // Calculate new score
            $newScore = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
            $newScore = round($newScore, 2);
            
            error_log("Recalculated score: $correctAnswers/$totalQuestions = $newScore%");
            
            // Update the stored score in exam_attempts table
            $updateResult = $this->examAttemptDAO->updateAttemptScore($attemptId, $newScore);
            
            if ($updateResult) {
                error_log("Successfully updated stored score to $newScore%");
                return [
                    'success' => true,
                    'message' => "Score updated to $newScore%",
                    'old_score' => $attemptDetails['score'],
                    'new_score' => $newScore,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update score in database'
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Error recalculating score: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error recalculating score: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get exam attempt by ID
     */
    public function getExamAttemptById($attemptId): ?array
    {
        try {
            // Query exam_attempts table with real database query
            $db = \App\Config\Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT ea.*, e.title as exam_title, u.full_name as student_name
                FROM exam_attempts ea
                LEFT JOIN exams e ON ea.exam_id = e.id
                LEFT JOIN users u ON ea.student_id = u.user_id
                WHERE ea.id = ?
            ");
            $stmt->execute([$attemptId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("Error getting exam attempt by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed exam attempt results (question-by-question breakdown)
     */
    public function getDetailedAttemptResults($attemptId): array
    {
        try {
            // Get detailed results with question-by-question breakdown
            // For now, return dummy data
            return [
                'correct_answers' => 8,
                'incorrect_answers' => 2,
                'unanswered' => 0,
                'points_earned' => 85,
                'questions' => [
                    [
                        'question_text' => 'What is the capital of France?',
                        'student_answer' => 'Paris',
                        'correct_answer' => 'Paris',
                        'is_correct' => true,
                        'points' => 10,
                        'explanation' => 'Paris is indeed the capital city of France.'
                    ],
                    [
                        'question_text' => 'What is 2 + 2?',
                        'student_answer' => '5',
                        'correct_answer' => '4',
                        'is_correct' => false,
                        'points' => 10,
                        'explanation' => 'The correct answer is 4. Basic arithmetic: 2 + 2 = 4.'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            error_log("Error getting detailed attempt results: " . $e->getMessage());
            return [];
        }
    }

/**
 * Calculate exam score
 * Returns array with detailed score information or legacy float for backward compatibility
 */
    private function calculateScore($answers, $attemptId)
    {
        try {
            error_log("=== CALCULATING SCORE ===");
            error_log("Answers to score: " . json_encode($answers));
            
            if (empty($answers)) {
                error_log("No answers provided, returning 0");
                return 0.0;
            }
            
            // Get the exam attempt to find exam ID
            $attempt = $this->examAttemptDAO->getAttemptById($attemptId);
            if (!$attempt) {
                error_log("No attempt found for ID: $attemptId");
                return 0.0;
            }
            
            $examId = $attempt['exam_id'];
            error_log("Exam ID: $examId");
            
            // Get all questions for this exam
            $questions = $this->getExamQuestions($examId);
            error_log("Found " . count($questions) . " questions for exam");
            
            if (empty($questions)) {
                error_log("No questions found for exam");
                return 0.0;
            }
            
            $pointsEarned = 0;
            $totalPoints = 0;
            
            foreach ($questions as $question) {
                $questionId = $question->getId();
                $questionPoints = $question->getPoints();
                $studentAnswer = $answers[$questionId] ?? null;
                
                // Add to total points regardless of answer
                $totalPoints += $questionPoints;
                
                if ($studentAnswer !== null) {
                    // Get correct answer for this question
                    $correctAnswer = $this->getCorrectAnswerForQuestion($questionId);
                    error_log("Question $questionId ({$questionPoints}pts) - Student: '$studentAnswer', Correct: '$correctAnswer'");
                    
                    if ($studentAnswer === $correctAnswer) {
                        $pointsEarned += $questionPoints;
                        error_log("Question $questionId: CORRECT (+{$questionPoints} points)");
                    } else {
                        error_log("Question $questionId: INCORRECT (0 points)");
                    }
                } else {
                    error_log("Question $questionId: NO ANSWER (0 points)");
                }
            }
            
            $percentage = $totalPoints > 0 ? ($pointsEarned / $totalPoints) * 100 : 0;
            error_log("Final score calculation: $pointsEarned/$totalPoints = $percentage%");
            
            // Return both raw score and percentage for real academic grading
            return [
                'percentage' => round($percentage, 2),
                'points_earned' => $pointsEarned,
                'total_points' => $totalPoints,
                'raw_score' => "$pointsEarned/$totalPoints"
            ];
        } catch (\Exception $e) {
            error_log("Error calculating score: " . $e->getMessage());
            return [
                'percentage' => 0.0,
                'points_earned' => 0,
                'total_points' => 0,
                'raw_score' => "0/0"
            ];
        }
    }
    
    /**
     * Get correct answer for a question
     */
    private function getCorrectAnswerForQuestion($questionId): ?string
    {
        try {
            // First, get the question to check its type
            $question = $this->questionDAO->getById($questionId);
            if (!$question) {
                error_log("Question not found: $questionId");
                return null;
            }
            
            $questionType = $question->getQuestionType();
            error_log("Question $questionId type: $questionType");
            
            if ($questionType === 'true_false') {
                // For true/false questions, get the correct answer from the question model
                $correctAnswer = $question->getCorrectAnswer() ?? 'true';
                error_log("True/False question $questionId correct answer: $correctAnswer");
                return $correctAnswer;
            } else {
                // For multiple choice questions, get correct answer from question_options
                $options = $this->getQuestionOptions($questionId);
                
                foreach ($options as $option) {
                    if ($option->getIsCorrect()) {
                        $correctText = $option->getOptionText();
                        error_log("Multiple choice question $questionId correct answer: $correctText");
                        return $correctText;
                    }
                }
                
                error_log("No correct option found for multiple choice question $questionId");
                return null;
            }
        } catch (\Exception $e) {
            error_log("Error getting correct answer for question $questionId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Compare year levels handling different formats
     */
    private function compareYearLevels($studentYearLevel, $examYearLevel): bool
    {
        // Normalize both year levels to compare
        $normalizedStudent = $this->normalizeYearLevel($studentYearLevel);
        $normalizedExam = $this->normalizeYearLevel($examYearLevel);
        
        error_log("Comparing year levels - Student: '$studentYearLevel' (normalized: '$normalizedStudent') vs Exam: '$examYearLevel' (normalized: '$normalizedExam') - Match: " . ($normalizedStudent === $normalizedExam ? 'YES' : 'NO'));
        
        return $normalizedStudent === $normalizedExam;
    }

    /**
     * Get comprehensive student exam details with 100% accuracy
     */
    public function getComprehensiveStudentExamDetails($attemptId): ?array
    {
        try {
            // Get exam attempt with student and exam info in one query
            $attemptQuery = "
                SELECT 
                    ea.id as attempt_id,
                    ea.exam_id,
                    ea.student_id,
                    ea.score as original_score,
                    ea.status,
                    ea.start_time,
                    ea.end_time,
                    ea.created_at,
                    e.title as exam_title,
                    u.full_name as student_name,
                    u.school_id as student_school_id
                FROM exam_attempts ea
                INNER JOIN exams e ON ea.exam_id = e.id
                INNER JOIN users u ON ea.student_id = u.user_id AND u.role = 'student'
                WHERE ea.id = ?
                LIMIT 1
            ";
            
            $stmt = $this->examDAO->getConnection()->prepare($attemptQuery);
            $stmt->execute([$attemptId]);
            $attempt = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$attempt) {
                return null;
            }
            
            // Get questions with answers, including faculty overrides
            $questionsQuery = "
                SELECT 
                    q.id as question_id,
                    q.question_text,
                    q.question_type,
                    q.points as question_points,
                    q.order_index,
                    sa.answer_text,
                    sa.selected_option_id,
                    sa.is_correct as stored_is_correct,
                    sa.points_earned,
                    sa.score as student_answer_score,
                    fso.new_score as override_score,
                    fso.reason as override_reason,
                    fso.overridden_at,
                    u_faculty.full_name as override_faculty_name
                FROM questions q
                LEFT JOIN student_answers sa ON q.id = sa.question_id AND sa.attempt_id = ?
                LEFT JOIN faculty_score_overrides fso ON sa.attempt_id = fso.attempt_id AND sa.question_id = fso.question_id
                LEFT JOIN users u_faculty ON fso.overridden_by = u_faculty.user_id
                WHERE q.exam_id = ?
                ORDER BY q.order_index, q.id
            ";
            
            $stmt = $this->examDAO->getConnection()->prepare($questionsQuery);
            $stmt->execute([$attemptId, $attempt['exam_id']]);
            $questions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Process questions with accurate scoring
            $processedQuestions = [];
            $actualCorrectCount = 0;
            $totalQuestions = count($questions);
            
            foreach ($questions as $question) {
                // Get options for this question
                $optionsQuery = "SELECT id, option_text, is_correct, order_index FROM question_options WHERE question_id = ? ORDER BY order_index";
                $optStmt = $this->examDAO->getConnection()->prepare($optionsQuery);
                $optStmt->execute([$question['question_id']]);
                $options = $optStmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $correctAnswer = '';
                $studentAnswerText = $question['answer_text'] ?? '';
                
                // Handle missing student_answers record
                $hasStudentAnswer = !empty($question['answer_text']) || !empty($question['selected_option_id']);
                
                // Find correct answer from options
                if (!empty($options)) {
                    foreach ($options as $opt) {
                        if ($opt['is_correct'] == '1') {
                            $correctAnswer = $opt['option_text'];
                            break;
                        }
                    }
                }
                
                // Process student answer
                if ($question['selected_option_id'] && !empty($options)) {
                    foreach ($options as $opt) {
                        if ($opt['id'] == $question['selected_option_id']) {
                            $studentAnswerText = $opt['option_text'];
                            break;
                        }
                    }
                }
                
                // Handle numeric answers for multiple choice
                if ($question['question_type'] === 'multiple_choice' && is_numeric($question['answer_text']) && !$question['selected_option_id']) {
                    $answerIndex = intval($question['answer_text']);
                    if (isset($options[$answerIndex])) {
                        $studentAnswerText = $options[$answerIndex]['option_text'];
                    }
                }
                
                // Smart True/False detection (including short_answer with true/false options)
                $isTrueFalseQuestion = ($question['question_type'] === 'true_false') || 
                                     (count($options) == 2 && 
                                      strtolower(trim($options[0]['option_text'])) === 'true' && 
                                      strtolower(trim($options[1]['option_text'])) === 'false');
                
                if ($isTrueFalseQuestion) {
                    // Normalize student answer
                    $studentLower = strtolower(trim($studentAnswerText));
                    if (in_array($studentLower, ['true', '1', 'yes', 't'])) {
                        $studentAnswerText = 'True';
                    } elseif (in_array($studentLower, ['false', '0', 'no', 'f'])) {
                        $studentAnswerText = 'False';
                    }
                    
                    // Normalize correct answer
                    $correctLower = strtolower(trim($correctAnswer));
                    if (in_array($correctLower, ['true', '1', 'yes', 't'])) {
                        $correctAnswer = 'True';
                    } elseif (in_array($correctLower, ['false', '0', 'no', 'f'])) {
                        $correctAnswer = 'False';
                    }
                }
                
                // Determine if answer is actually correct
                $isActuallyCorrect = false;
                
                if (!$hasStudentAnswer) {
                    // No student answer recorded - treat as incorrect
                    $isActuallyCorrect = false;
                    $studentAnswerText = 'No answer recorded';
                } elseif ($question['question_type'] === 'multiple_choice') {
                    if ($question['selected_option_id'] && $correctAnswer) {
                        foreach ($options as $opt) {
                            if ($opt['id'] == $question['selected_option_id'] && $opt['is_correct'] == '1') {
                                $isActuallyCorrect = true;
                                break;
                            }
                        }
                    } elseif ($studentAnswerText && $correctAnswer) {
                        $isActuallyCorrect = (strcasecmp(trim($studentAnswerText), trim($correctAnswer)) === 0);
                    }
                } elseif ($isTrueFalseQuestion) {
                    if ($studentAnswerText && $correctAnswer) {
                        $isActuallyCorrect = (strcasecmp(trim($studentAnswerText), trim($correctAnswer)) === 0);
                    }
                } else {
                    // For other types, check if answer matches any correct option
                    if (!empty($options) && $studentAnswerText) {
                        foreach ($options as $opt) {
                            if ($opt['is_correct'] == '1' && strcasecmp(trim($studentAnswerText), trim($opt['option_text'])) === 0) {
                                $isActuallyCorrect = true;
                                break;
                            }
                        }
                    } else {
                        $isActuallyCorrect = (bool)$question['stored_is_correct'];
                    }
                }
                
                // Handle faculty override
                $currentScore = $isActuallyCorrect ? $question['question_points'] : 0;
                $facultyOverride = null;
                
                if ($question['override_score'] !== null) {
                    $currentScore = (float)$question['override_score'];
                    $isActuallyCorrect = $currentScore > 0;
                    $facultyOverride = [
                        'overridden' => true,
                        'original_score' => $question['student_answer_score'] ?? 0,
                        'new_score' => $currentScore,
                        'reason' => $question['override_reason'],
                        'faculty_name' => $question['override_faculty_name'],
                        'overridden_at' => $question['overridden_at']
                    ];
                }
                
                if ($isActuallyCorrect) {
                    $actualCorrectCount++;
                }
                
                // Get AI grading data for essays (if not already handled by override)
                $aiGrading = null;
                if ($question['question_type'] === 'essay' && !$facultyOverride) {
                    // Get AI grading result
                    $aiGrading = $this->getAIGradingResult($attemptId, $question['question_id']);
                    
                    // Use AI score if available
                    if ($aiGrading) {
                        $currentScore = $aiGrading['ai_score'];
                        $isActuallyCorrect = $currentScore > 0;
                    }
                }
                
                $processedQuestions[] = [
                    'question_id' => (int)$question['question_id'],
                    'question_text' => $question['question_text'],
                    'question_type' => $question['question_type'],
                    'student_answer' => $studentAnswerText ?: 'No answer',
                    'correct_answer' => $correctAnswer ?: 'N/A',
                    'is_correct' => $isActuallyCorrect,
                    'points' => $question['question_points'],
                    'max_points' => $question['question_points'],
                    'score' => $currentScore,
                    'points_earned' => $currentScore,
                    'ai_grading' => $aiGrading,
                    'faculty_override' => $facultyOverride,
                    'has_student_answer' => $hasStudentAnswer
                ];
            }
            
            // Calculate accurate score and time
            $accurateScore = $totalQuestions > 0 ? round(($actualCorrectCount / $totalQuestions) * 100, 2) : 0;
            
            $timeTaken = 'N/A';
            if ($attempt['start_time'] && $attempt['end_time']) {
                $start = new \DateTime($attempt['start_time']);
                $end = new \DateTime($attempt['end_time']);
                $diff = $start->diff($end);
                
                if ($diff->h > 0) {
                    $timeTaken = sprintf('%d hour%s, %d minute%s', 
                        $diff->h, $diff->h > 1 ? 's' : '',
                        $diff->i, $diff->i != 1 ? 's' : '');
                } elseif ($diff->i > 0) {
                    $timeTaken = sprintf('%d minute%s, %d second%s', 
                        $diff->i, $diff->i > 1 ? 's' : '',
                        $diff->s, $diff->s != 1 ? 's' : '');
                } else {
                    $timeTaken = sprintf('%d second%s', 
                        $diff->s, $diff->s != 1 ? 's' : '');
                }
            }
            
            return [
                'exam_id' => $attempt['exam_id'],
                'attempt_id' => $attempt['attempt_id'],
                'student_name' => $attempt['student_name'],
                'student_id' => $attempt['student_school_id'],
                'exam_title' => $attempt['exam_title'],
                'score' => $accurateScore,
                'original_score' => round(floatval($attempt['original_score']), 2),
                'correct_answers' => $actualCorrectCount,
                'total_questions' => $totalQuestions,
                'completed_at' => $attempt['end_time'] ?: $attempt['created_at'],
                'time_taken' => $timeTaken,
                'status' => $attempt['status'],
                'questions' => $processedQuestions
            ];
            
        } catch (\Exception $e) {
            error_log("Error getting comprehensive student exam details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize year level to a standard format
     */
    private function normalizeYearLevel($yearLevel): string
    {
        if (empty($yearLevel)) {
            return '';
        }
        
        $yearLevel = trim($yearLevel);
        
        // Handle numeric formats: "1", "2", "3", "4"
        if (is_numeric($yearLevel)) {
            $number = (int)$yearLevel;
            $suffix = 'th';
            
            if ($number == 1) $suffix = 'st';
            elseif ($number == 2) $suffix = 'nd';
            elseif ($number == 3) $suffix = 'rd';
            
            return $number . $suffix . ' Year';
        }
        
        // Handle ordinal formats: "1st", "2nd", "3rd", "4th"
        if (preg_match('/^(\d+)(st|nd|rd|th)$/', $yearLevel, $matches)) {
            return $yearLevel . ' Year';
        }
        
        // Handle full formats: "1st Year", "2nd Year", etc.
        if (preg_match('/^(\d+)(st|nd|rd|th)\s+Year$/i', $yearLevel)) {
            return $yearLevel;
        }
        
        // Try to extract number and convert to standard format
        if (preg_match('/(\d+)/', $yearLevel, $matches)) {
            $number = $matches[1];
            $suffix = 'th';
            
            if ($number == 1) $suffix = 'st';
            elseif ($number == 2) $suffix = 'nd';
            elseif ($number == 3) $suffix = 'rd';
            
            return $number . $suffix . ' Year';
        }
        
        // Return as-is if no pattern matches
        return $yearLevel;
    }

    /**
     * Get question by ID
     */
    public function getQuestionById($questionId)
    {
        try {
            // Use direct database query since findById doesn't exist
            $db = \App\Config\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM questions WHERE id = ?");
            $stmt->execute([$questionId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error getting question by ID: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Override question score for a specific attempt
     */
    public function overrideQuestionScore($attemptId, $questionId, $newScore, $reason, $facultyId)
    {
        try {
            error_log("=== OVERRIDE QUESTION SCORE START ===");
            error_log("Attempt ID: $attemptId, Question ID: $questionId, New Score: $newScore");
            
            // First, check if there's already an override record
            $db = \App\Config\Database::getInstance()->getConnection();
            
            // Check if override already exists
            $stmt = $db->prepare("
                SELECT id FROM faculty_score_overrides 
                WHERE attempt_id = ? AND question_id = ?
            ");
            $stmt->execute([$attemptId, $questionId]);
            $existingOverride = $stmt->fetch();
            
            if ($existingOverride) {
                error_log("Updating existing override");
                // Update existing override
                $stmt = $db->prepare("
                    UPDATE faculty_score_overrides 
                    SET new_score = ?, reason = ?, overridden_by = ?, overridden_at = NOW()
                    WHERE attempt_id = ? AND question_id = ?
                ");
                $result = $stmt->execute([$newScore, $reason, $facultyId, $attemptId, $questionId]);
            } else {
                error_log("Creating new override record");
                // Get original AI score if it exists
                $stmt = $db->prepare("
                    SELECT score FROM student_answers 
                    WHERE attempt_id = ? AND question_id = ?
                ");
                $stmt->execute([$attemptId, $questionId]);
                $originalAnswer = $stmt->fetch();
                $originalScore = $originalAnswer ? $originalAnswer['score'] : 0;
                
                error_log("Original score: $originalScore");
                
                // Create new override record
                $stmt = $db->prepare("
                    INSERT INTO faculty_score_overrides 
                    (attempt_id, question_id, original_score, new_score, reason, overridden_by, overridden_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([$attemptId, $questionId, $originalScore, $newScore, $reason, $facultyId]);
            }
            
            if ($result) {
                error_log("Override record saved successfully");
                
                // Check if student_answers record exists, if not create it
                $stmt = $db->prepare("
                    SELECT id FROM student_answers 
                    WHERE attempt_id = ? AND question_id = ?
                ");
                $stmt->execute([$attemptId, $questionId]);
                $answerExists = $stmt->fetch();
                
                if ($answerExists) {
                    // Update existing student_answers record
                    $stmt = $db->prepare("
                        UPDATE student_answers 
                        SET score = ?, is_correct = (? > 0)
                        WHERE attempt_id = ? AND question_id = ?
                    ");
                    $updateResult = $stmt->execute([$newScore, $newScore, $attemptId, $questionId]);
                    error_log("Updated existing student_answers record: " . ($updateResult ? 'success' : 'failed'));
                } else {
                    // Create student_answers record if it doesn't exist
                    error_log("Creating missing student_answers record");
                    $stmt = $db->prepare("
                        INSERT INTO student_answers 
                        (attempt_id, question_id, answer_text, score, is_correct, created_at)
                        VALUES (?, ?, 'Faculty Override', ?, (? > 0), NOW())
                    ");
                    $insertResult = $stmt->execute([$attemptId, $questionId, $newScore, $newScore]);
                    error_log("Created student_answers record: " . ($insertResult ? 'success' : 'failed'));
                }
                
                // Recalculate total exam score
                error_log("Recalculating exam score");
                $recalcResult = $this->recalculateExamScore($attemptId);
                error_log("Recalculation result: " . json_encode($recalcResult));
                
                return true;
            }
            
            error_log("Failed to save override record");
            return false;
            
        } catch (\Exception $e) {
            error_log("=== OVERRIDE ERROR ===");
            error_log("Error overriding question score: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

/**
 * Process answers with AI grading for essays
 */
private function processAnswersWithAI($attemptId, $answers)
{
    try {
        // Get exam attempt to find exam ID
        $attempt = $this->examAttemptDAO->getAttemptById($attemptId);
        if (!$attempt) {
            return;
        }
        
        // Get exam questions
        $questions = $this->getExamQuestions($attempt['exam_id']);
        
        // Initialize AI service
        $aiService = new \App\Services\AIEssayService();
        
        foreach ($answers as $questionId => $answer) {
            // Find the question
            $question = null;
            foreach ($questions as $q) {
                if ($q->getId() == $questionId) {
                    $question = $q;
                    break;
                }
            }
            
            if ($question && $question->getQuestionType() === 'essay' && !empty($answer)) {
                // Get AI configuration from question
                $aiConfig = $this->getQuestionAIConfig($questionId);
                
                if ($aiConfig && $aiConfig['grading_method'] !== 'manual') {
                    // Grade essay with AI
                    $maxPoints = $question->getPoints() ?? 10;
                    $gradingResult = $aiService->gradeEssay($answer, $aiConfig, $maxPoints);
                    
                    // Save AI grading result
                    $this->saveAIGradingResult($attemptId, $questionId, $gradingResult);
                    
                    // Update student answer with AI score
                    $this->updateStudentAnswerScore($attemptId, $questionId, $gradingResult['ai_score']);
                }
            }
        }
    } catch (\Exception $e) {
        error_log("Error processing answers with AI: " . $e->getMessage());
    }
}

/**
 * Get AI configuration for a question
 */
private function getQuestionAIConfig($questionId)
{
    try {
        $db = \App\Config\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT ai_config FROM questions WHERE id = ?");
        $stmt->execute([$questionId]);
        $result = $stmt->fetch();
        
        if ($result && $result['ai_config']) {
            return json_decode($result['ai_config'], true);
        }
        
        return null;
    } catch (\Exception $e) {
        error_log("Error getting question AI config: " . $e->getMessage());
        return null;
    }
}

/**
 * Save AI grading result
 */
private function saveAIGradingResult($attemptId, $questionId, $gradingResult)
{
    try {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO ai_grading_results 
            (attempt_id, question_id, ai_score, max_points, confidence, criterion_scores, 
             overall_feedback, strengths, improvements, requires_manual_review, review_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            ai_score = VALUES(ai_score),
            confidence = VALUES(confidence),
            criterion_scores = VALUES(criterion_scores),
            overall_feedback = VALUES(overall_feedback),
            strengths = VALUES(strengths),
            improvements = VALUES(improvements),
            requires_manual_review = VALUES(requires_manual_review),
            review_reason = VALUES(review_reason)
        ");
        
        $stmt->execute([
            $attemptId,
            $questionId,
            $gradingResult['ai_score'] ?? 0,
            $gradingResult['max_points'] ?? 10,
            $gradingResult['confidence'] ?? 0,
            json_encode($gradingResult['criterion_scores'] ?? []),
            $gradingResult['overall_feedback'] ?? '',
            json_encode($gradingResult['strengths'] ?? []),
            json_encode($gradingResult['improvements'] ?? []),
            $gradingResult['requires_manual_review'] ? 1 : 0,
            $gradingResult['review_reason'] ?? ''
        ]);
        
    } catch (\Exception $e) {
        error_log("Error saving AI grading result: " . $e->getMessage());
    }
}

/**
 * Update student answer score
 */
private function updateStudentAnswerScore($attemptId, $questionId, $score)
{
    try {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            UPDATE student_answers 
            SET score = ?, is_correct = (? > 0)
            WHERE attempt_id = ? AND question_id = ?
        ");
        
        $stmt->execute([$score, $score, $attemptId, $questionId]);
        
    } catch (\Exception $e) {
        error_log("Error updating student answer score: " . $e->getMessage());
    }
}

/**
 * Get AI grading result for a question
 */
private function getAIGradingResult($attemptId, $questionId)
{
    try {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT ai_score, max_points, confidence, criterion_scores, 
                   overall_feedback, strengths, improvements, requires_manual_review, 
                   review_reason, graded_at
            FROM ai_grading_results 
            WHERE attempt_id = ? AND question_id = ?
        ");
        
        $stmt->execute([$attemptId, $questionId]);
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'graded_by_ai' => true,
                'ai_score' => (float)$result['ai_score'],
                'max_points' => (float)$result['max_points'],
                'confidence' => (int)$result['confidence'],
                'criterion_scores' => json_decode($result['criterion_scores'], true),
                'overall_feedback' => $result['overall_feedback'],
                'strengths' => json_decode($result['strengths'], true),
                'improvements' => json_decode($result['improvements'], true),
                'requires_manual_review' => (bool)$result['requires_manual_review'],
                'review_reason' => $result['review_reason'],
                'graded_at' => $result['graded_at']
            ];
        }
        
        return null;
    } catch (\Exception $e) {
        error_log("Error getting AI grading result: " . $e->getMessage());
        return null;
    }
}

/**
 * Get faculty override for a question
 */
private function getFacultyOverride($attemptId, $questionId)
{
    try {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT fso.original_score, fso.new_score, fso.reason, 
                   fso.overridden_at, u.full_name as faculty_name
            FROM faculty_score_overrides fso
            INNER JOIN users u ON fso.overridden_by = u.user_id
            WHERE fso.attempt_id = ? AND fso.question_id = ?
        ");
        
        $stmt->execute([$attemptId, $questionId]);
        $result = $stmt->fetch();
        
        if ($result) {
            return [
                'overridden' => true,
                'original_score' => (float)$result['original_score'],
                'new_score' => (float)$result['new_score'],
                'reason' => $result['reason'],
                'faculty_name' => $result['faculty_name'],
                'overridden_at' => $result['overridden_at']
            ];
        }
        
        return null;
    } catch (\Exception $e) {
        error_log("Error getting faculty override: " . $e->getMessage());
        return null;
    }
}
}
