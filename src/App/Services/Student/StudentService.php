<?php

namespace App\Services\Student;

use App\DAO\Auth\UserDAO;
use App\DAO\Assignment\AssignmentDAO;
use Exception;

class StudentService
{
    private UserDAO $userDAO;
    private AssignmentDAO $assignmentDAO;

    public function __construct(
        UserDAO $userDAO = null,
        AssignmentDAO $assignmentDAO = null
    ) {
        $this->userDAO = $userDAO ?? new UserDAO();
        $this->assignmentDAO = $assignmentDAO ?? new AssignmentDAO();
    }

    /**
     * Get students for a specific faculty's subject assignment
     */
    public function getStudentsForAssignment($assignmentId)
    {
        try {
            // Get the assignment details
            $assignment = $this->assignmentDAO->getById($assignmentId);
            if (!$assignment) {
                return [];
            }

            // Get students matching the year level and section
            return $this->userDAO->getStudentsByYearAndSection(
                $assignment->getYearLevel(),
                $assignment->getSection()
            );
        } catch (Exception $e) {
            error_log("Error getting students for assignment: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all students for a faculty member (across all their subjects)
     */
    public function getStudentsForFaculty($facultyId)
    {
        try {
            // Get all assignments for this faculty
            $assignments = $this->assignmentDAO->getByFilters(['faculty_id' => $facultyId]);
            
            $allStudents = [];
            $seenStudents = [];

            foreach ($assignments as $assignment) {
                $students = $this->userDAO->getStudentsByYearAndSection(
                    $assignment->getYearLevel(),
                    $assignment->getSection()
                );

                foreach ($students as $student) {
                    $studentId = $student->getUserId();
                    if (!isset($seenStudents[$studentId])) {
                        $seenStudents[$studentId] = true;
                        $student->setSubjectInfo([
                            'subject_code' => $assignment->toArray()['subject_code'] ?? '',
                            'subject_name' => $assignment->toArray()['subject_name'] ?? '',
                            'year_level' => $assignment->getYearLevel(),
                            'section' => $assignment->getSection(),
                            'semester' => $assignment->getSemester(),
                            'academic_year' => $assignment->getAcademicYear()
                        ]);
                        $allStudents[] = $student;
                    }
                }
            }

            return $allStudents;
        } catch (Exception $e) {
            error_log("Error getting students for faculty: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get students by subject ID for a faculty
     */
    public function getStudentsForSubject($subjectId, $facultyId)
    {
        try {
            // Get assignment for this subject and faculty
            $assignments = $this->assignmentDAO->getByFilters([
                'subject_id' => $subjectId,
                'faculty_id' => $facultyId
            ]);

            if (empty($assignments)) {
                return [];
            }

            $assignment = $assignments[0]; // Take first assignment
            
            return $this->userDAO->getStudentsByYearAndSection(
                $assignment->getYearLevel(),
                $assignment->getSection()
            );
        } catch (Exception $e) {
            error_log("Error getting students for subject: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get student statistics for a faculty
     */
    public function getStudentStats($facultyId)
    {
        try {
            $students = $this->getStudentsForFaculty($facultyId);
            
            $stats = [
                'total_students' => count($students),
                'by_year_level' => [],
                'by_section' => []
            ];

            foreach ($students as $student) {
                $yearLevel = $student->getYearLevel();
                $section = $student->getSection();

                if (!isset($stats['by_year_level'][$yearLevel])) {
                    $stats['by_year_level'][$yearLevel] = 0;
                }
                $stats['by_year_level'][$yearLevel]++;

                if (!isset($stats['by_section'][$section])) {
                    $stats['by_section'][$section] = 0;
                }
                $stats['by_section'][$section]++;
            }

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting student stats: " . $e->getMessage());
            return [
                'total_students' => 0,
                'by_year_level' => [],
                'by_section' => []
            ];
        }
    }
}
