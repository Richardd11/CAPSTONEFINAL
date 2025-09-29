<?php

namespace App\DAO\Exam;

use App\Models\Exam;
use App\Config\Database;
use App\Interfaces\ExamDAOInterface;
use PDO;

class ExamDAO implements ExamDAOInterface
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get database connection
     */
    public function getConnection()
    {
        return $this->db;
    }

    /**
     * Create a new exam
     */
    public function create(Exam $exam)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO exams (
                    title, description, subject_id, faculty_id, year_level, section,
                    academic_year, semester, exam_type, time_limit, total_points,
                    instructions, is_active, start_date, end_date, allow_retakes,
                    max_attempts, show_results, randomize_questions
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $exam->getTitle(),
                $exam->getDescription(),
                $exam->getSubjectId(),
                $exam->getFacultyId(),
                $exam->getYearLevel(),
                $exam->getSection(),
                $exam->getAcademicYear(),
                $exam->getSemester(),
                $exam->getExamType(),
                $exam->getTimeLimit(),
                $exam->getTotalPoints(),
                $exam->getInstructions(),
                $exam->getIsActive() ? 1 : 0,
                $exam->getStartDate(),
                $exam->getEndDate(),
                $exam->getAllowRetakes() ? 1 : 0,
                $exam->getMaxAttempts(),
                $exam->getShowResults() ? 1 : 0,
                $exam->getRandomizeQuestions() ? 1 : 0
            ]);
            
            if ($result) {
                $exam->setId($this->db->lastInsertId());
                return $exam;
            }
            
            return null;
        } catch (\PDOException $e) {
            error_log("Error creating exam: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing exam
     */
    public function update(Exam $exam)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE exams SET
                    title = ?, description = ?, subject_id = ?, year_level = ?, section = ?,
                    academic_year = ?, semester = ?, exam_type = ?, time_limit = ?, total_points = ?,
                    instructions = ?, is_active = ?, start_date = ?, end_date = ?, allow_retakes = ?,
                    max_attempts = ?, show_results = ?, randomize_questions = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $exam->getTitle(),
                $exam->getDescription(),
                $exam->getSubjectId(),
                $exam->getYearLevel(),
                $exam->getSection(),
                $exam->getAcademicYear(),
                $exam->getSemester(),
                $exam->getExamType(),
                $exam->getTimeLimit(),
                $exam->getTotalPoints(),
                $exam->getInstructions(),
                $exam->getIsActive() ? 1 : 0,
                $exam->getStartDate(),
                $exam->getEndDate(),
                $exam->getAllowRetakes() ? 1 : 0,
                $exam->getMaxAttempts(),
                $exam->getShowResults() ? 1 : 0,
                $exam->getRandomizeQuestions() ? 1 : 0,
                $exam->getId()
            ]);
        } catch (\PDOException $e) {
            error_log("Error updating exam: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an exam
     */
    public function delete($examId)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM exams WHERE id = ?");
            return $stmt->execute([$examId]);
        } catch (\PDOException $e) {
            error_log("Error deleting exam: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exam by ID
     */
    public function getById($examId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM exams e
                JOIN subjects s ON e.subject_id = s.subject_id
                JOIN users u ON e.faculty_id = u.user_id
                WHERE e.id = ?
            ");
            $stmt->execute([$examId]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? new Exam($row) : null;
        } catch (\PDOException $e) {
            error_log("Error getting exam by ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all exams
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT e.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM exams e
                JOIN subjects s ON e.subject_id = s.subject_id
                JOIN users u ON e.faculty_id = u.user_id
                ORDER BY e.created_at DESC
            ");
            $stmt->execute();
            
            $exams = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $exams[] = new Exam($row);
            }
            
            return $exams;
        } catch (\PDOException $e) {
            error_log("Error getting all exams: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exams by faculty
     */
    public function getByFaculty($facultyId)
    {
        try {
            error_log("=== EXAM DAO: Getting exams for faculty ID: $facultyId ===");
            
            $stmt = $this->db->prepare("
                SELECT e.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM exams e
                JOIN subjects s ON e.subject_id = s.subject_id
                JOIN users u ON e.faculty_id = u.user_id
                WHERE e.faculty_id = ?
                ORDER BY e.created_at DESC
            ");
            $stmt->execute([$facultyId]);
            
            error_log("Query executed successfully");
            
            $exams = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                error_log("Found exam: " . $row['title'] . " (ID: " . $row['id'] . ")");
                $exams[] = new Exam($row);
            }
            
            error_log("Total exams found: " . count($exams));
            return $exams;
        } catch (\PDOException $e) {
            error_log("Error getting exams by faculty: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exams by filters
     */
    public function getByFilters($filters = [])
    {
        try {
            $sql = "
                SELECT e.*, s.subject_code, s.subject_name, u.full_name as faculty_name
                FROM exams e
                JOIN subjects s ON e.subject_id = s.subject_id
                JOIN users u ON e.faculty_id = u.user_id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filters['faculty_id'])) {
                $sql .= " AND e.faculty_id = ?";
                $params[] = $filters['faculty_id'];
            }

            if (!empty($filters['subject_id'])) {
                $sql .= " AND e.subject_id = ?";
                $params[] = $filters['subject_id'];
            }

            if (!empty($filters['exam_type'])) {
                $sql .= " AND e.exam_type = ?";
                $params[] = $filters['exam_type'];
            }

            if (!empty($filters['year_level'])) {
                $sql .= " AND e.year_level = ?";
                $params[] = $filters['year_level'];
            }

            if (!empty($filters['section'])) {
                $sql .= " AND e.section = ?";
                $params[] = $filters['section'];
            }

            if (!empty($filters['academic_year'])) {
                $sql .= " AND e.academic_year = ?";
                $params[] = $filters['academic_year'];
            }

            if (!empty($filters['semester'])) {
                $sql .= " AND e.semester = ?";
                $params[] = $filters['semester'];
            }

            if (isset($filters['is_active'])) {
                $sql .= " AND e.is_active = ?";
                $params[] = $filters['is_active'] ? 1 : 0;
            }

            $sql .= " ORDER BY e.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $exams = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $exams[] = new Exam($row);
            }
            
            return $exams;
        } catch (\PDOException $e) {
            error_log("Error getting exams by filters: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exam statistics
     */
    public function getExamStats($facultyId = null)
    {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_exams,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_exams,
                    COUNT(CASE WHEN exam_type = 'quiz' THEN 1 END) as quizzes,
                    COUNT(CASE WHEN exam_type = 'midterm' THEN 1 END) as midterms,
                    COUNT(CASE WHEN exam_type = 'final' THEN 1 END) as finals,
                    AVG(total_points) as avg_points
                FROM exams
            ";
            $params = [];

            if ($facultyId) {
                $sql .= " WHERE faculty_id = ?";
                $params[] = $facultyId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error getting exam stats: " . $e->getMessage());
            throw $e;
        }
    }
}
