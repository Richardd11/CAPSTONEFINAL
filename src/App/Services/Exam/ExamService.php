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
            // Create exam model
            $exam = new Exam($data);
            
            // Validate exam data
            $errors = $exam->validate();
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $errors)
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
            return [
                'success' => false,
                'message' => 'An error occurred while creating the exam.'
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

            // Update exam data
            $exam = new Exam(array_merge($existingExam->toArray(), $data));
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
            $result = $this->examDAO->update($exam);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Exam updated successfully.',
                    'data' => $exam->toArray()
                ];
            }

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
            // Get real exam results from database using ExamAttemptDAO
            $results = $this->examAttemptDAO->getExamResults($examId, $studentId);
            
            // Format the results for the frontend
            $formattedResults = [];
            foreach ($results as $result) {
                $formattedResults[] = [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'student_id' => $result['student_id'],
                    'score' => (float)$result['score'],
                    'completed_at' => $result['completed_at'],
                    'status' => $result['status']
                ];
            }
            
            return $formattedResults;
        } catch (\Exception $e) {
            error_log("Error getting detailed exam results: " . $e->getMessage());
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
        
        // Calculate score based on answers
        $score = $this->calculateScore($answers, $attemptId);
        error_log("Calculated score: $score");
        
        // Mark exam attempt as completed
        $completed = $this->examAttemptDAO->completeAttempt($attemptId, $score, $answers);
        error_log("Attempt completion result: " . ($completed ? 'success' : 'failed'));
        
        if ($completed) {
            return [
                'success' => true,
                'score' => $score,
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
            // Query exam_attempts table
            // For now, return dummy data based on attempt ID
            $attempts = [
                1 => [
                    'id' => 1,
                    'student_id' => 1,
                    'exam_id' => 1,
                    'score' => 85,
                    'status' => 'completed',
                    'completed_at' => '2024-09-20 14:30:00',
                    'duration' => 45
                ],
                2 => [
                    'id' => 2,
                    'student_id' => 1,
                    'exam_id' => 2,
                    'score' => 92,
                    'status' => 'completed',
                    'completed_at' => '2024-09-18 10:15:00',
                    'duration' => 60
                ],
                3 => [
                    'id' => 3,
                    'student_id' => 1,
                    'exam_id' => 3,
                    'score' => 78,
                    'status' => 'completed',
                    'completed_at' => '2024-09-15 16:45:00',
                    'duration' => 30
                ]
            ];
            
            return $attempts[$attemptId] ?? null;
        } catch (\Exception $e) {
            error_log("Error getting exam attempt: " . $e->getMessage());
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
 */
    private function calculateScore($answers, $attemptId): float
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
            
            $correctAnswers = 0;
            $totalQuestions = count($questions);
            
            foreach ($questions as $question) {
                $questionId = $question->getId();
                $studentAnswer = $answers[$questionId] ?? null;
                
                if ($studentAnswer !== null) {
                    // Get correct answer for this question
                    $correctAnswer = $this->getCorrectAnswerForQuestion($questionId);
                    error_log("Question $questionId - Student: '$studentAnswer', Correct: '$correctAnswer'");
                    
                    if ($studentAnswer === $correctAnswer) {
                        $correctAnswers++;
                        error_log("Question $questionId: CORRECT");
                    } else {
                        error_log("Question $questionId: INCORRECT");
                    }
                } else {
                    error_log("Question $questionId: NO ANSWER");
                }
            }
            
            $score = ($correctAnswers / $totalQuestions) * 100;
            error_log("Final score calculation: $correctAnswers/$totalQuestions = $score%");
            
            return round($score, 2);
        } catch (\Exception $e) {
            error_log("Error calculating score: " . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Get correct answer for a question
     */
    private function getCorrectAnswerForQuestion($questionId): ?string
    {
        try {
            // Get correct answer from question_options
            $options = $this->getQuestionOptions($questionId);
            
            foreach ($options as $option) {
                if ($option->getIsCorrect()) {
                    return $option->getOptionText();
                }
            }
            
            // If no options found, might be a True/False question - assume "true" is correct
            return "true";
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
            
            // Get questions with answers
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
                    sa.points_earned
                FROM questions q
                LEFT JOIN student_answers sa ON q.id = sa.question_id AND sa.attempt_id = ?
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
                
                // Find correct answer and process student answer
                foreach ($options as $opt) {
                    if ($opt['is_correct'] == '1') {
                        $correctAnswer = $opt['option_text'];
                    }
                    
                    if ($question['selected_option_id'] && $opt['id'] == $question['selected_option_id']) {
                        $studentAnswerText = $opt['option_text'];
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
                
                if ($question['question_type'] === 'multiple_choice') {
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
                
                if ($isActuallyCorrect) {
                    $actualCorrectCount++;
                }
                
                $processedQuestions[] = [
                    'question_id' => $question['question_id'],
                    'question_text' => $question['question_text'],
                    'question_type' => $question['question_type'],
                    'student_answer' => $studentAnswerText ?: 'No answer',
                    'correct_answer' => $correctAnswer ?: 'N/A',
                    'is_correct' => $isActuallyCorrect,
                    'points' => $question['question_points'],
                    'points_earned' => $isActuallyCorrect ? $question['question_points'] : 0
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
}
