/**
 * FacultyExamResultsService - Service Layer
 * Handles all API calls and business logic for exam results
 * NO DOM MANIPULATION - Pure data operations
 */
class FacultyExamResultsService {
    constructor() {
        this.baseUrl = '/faculty/api';
    }

    /**
     * Fetch all exams
     */
    async fetchExams() {
        try {
            const response = await fetch(`${this.baseUrl}/exams`);
            const data = await response.json();
            
            if (data.success && data.exams) {
                return { success: true, exams: data.exams };
            }
            return { success: false, error: 'No exams found' };
        } catch (error) {
            console.error('Error fetching exams:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Fetch results for a specific exam
     */
    async fetchExamResults(examId) {
        try {
            const response = await fetch(`${this.baseUrl}/exam/${examId}/results`);
            const data = await response.json();
            
            if (data.success && data.results) {
                return { success: true, results: data.results };
            }
            return { success: false, results: [] };
        } catch (error) {
            console.error('Error fetching exam results:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Fetch detailed student exam attempt
     */
    async fetchStudentDetails(attemptId) {
        try {
            const response = await fetch(`${this.baseUrl}/student-exam-details/${attemptId}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                return { success: true, data: data.data };
            }
            return { success: false, error: 'Details not found' };
        } catch (error) {
            console.error('Error fetching student details:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Submit faculty score override
     */
    async submitScoreOverride(attemptId, questionId, newScore, reason) {
        try {
            const response = await fetch(`${this.baseUrl}/override-score`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    attempt_id: attemptId,
                    question_id: questionId,
                    new_score: newScore,
                    reason: reason
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error submitting override:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Filter exams by subject code
     */
    filterExamsBySubject(exams, subjectCode) {
        if (!subjectCode) return exams;
        
        return exams.filter(exam => 
            exam.subject && exam.subject.toLowerCase() === subjectCode.toLowerCase()
        );
    }

    /**
     * Group exams by subject
     */
    groupExamsBySubject(exams) {
        const grouped = {};
        
        exams.forEach(exam => {
            const subject = exam.subject || 'General';
            if (!grouped[subject]) {
                grouped[subject] = [];
            }
            grouped[subject].push(exam);
        });
        
        return grouped;
    }

    /**
     * Sort results by score (highest first)
     */
    sortResultsByScore(results) {
        return [...results].sort((a, b) => (parseFloat(b.score) || 0) - (parseFloat(a.score) || 0));
    }

    /**
     * Calculate exam statistics
     */
    calculateStatistics(results) {
        if (!results || results.length === 0) {
            return {
                totalStudents: 0,
                averageScore: 0,
                highestScore: 0,
                lowestScore: 0,
                passRate: 0
            };
        }

        const totalStudents = results.length;
        const scores = results.map(r => parseFloat(r.score) || 0);
        const averageScore = scores.reduce((sum, score) => sum + score, 0) / totalStudents;
        const highestScore = Math.max(...scores);
        const lowestScore = Math.min(...scores);
        const passRate = (results.filter(r => (parseFloat(r.score) || 0) >= 75).length / totalStudents * 100);

        return {
            totalStudents,
            averageScore,
            highestScore,
            lowestScore,
            passRate
        };
    }

    /**
     * Get letter grade from score
     */
    getGrade(score) {
        if (score >= 95) return 'A+';
        if (score >= 90) return 'A';
        if (score >= 85) return 'B+';
        if (score >= 80) return 'B';
        if (score >= 75) return 'C+';
        if (score >= 70) return 'C';
        if (score >= 65) return 'D';
        return 'F';
    }

    /**
     * Get grade color classes
     */
    getGradeColor(score) {
        if (score >= 90) return 'bg-green-100 text-green-700';
        if (score >= 80) return 'bg-blue-100 text-blue-700';
        if (score >= 70) return 'bg-yellow-100 text-yellow-700';
        return 'bg-red-100 text-red-700';
    }

    /**
     * Get status text
     */
    getStatus(score) {
        return score >= 75 ? 'Satisfactory' : 'Needs Improvement';
    }

    /**
     * Calculate time taken
     */
    calculateTimeTaken(data) {
        // Try to calculate from start_time and end_time if available
        if (data.start_time && data.end_time) {
            const startTime = new Date(data.start_time);
            const endTime = new Date(data.end_time);
            const diffMs = endTime - startTime;
            
            if (diffMs > 0) {
                const hours = Math.floor(diffMs / (1000 * 60 * 60));
                const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diffMs % (1000 * 60)) / 1000);
                
                if (hours > 0) {
                    return `${hours}h ${minutes}m ${seconds}s`;
                } else if (minutes > 0) {
                    return `${minutes}m ${seconds}s`;
                } else {
                    return `${seconds}s`;
                }
            }
        }
        
        // Fallback to time_taken field if available
        if (data.time_taken) {
            return data.time_taken;
        }
        
        // If we have duration in minutes
        if (data.duration_minutes) {
            const minutes = parseInt(data.duration_minutes);
            if (minutes >= 60) {
                const hours = Math.floor(minutes / 60);
                const remainingMinutes = minutes % 60;
                return `${hours}h ${remainingMinutes}m`;
            } else {
                return `${minutes}m`;
            }
        }
        
        return 'N/A';
    }

    /**
     * Validate override inputs
     */
    validateOverride(newScore, reason, maxPoints) {
        const errors = [];

        if (!reason || reason.trim() === '') {
            errors.push('Please provide a reason for the override');
        }

        if (isNaN(newScore) || newScore < 0 || newScore > maxPoints) {
            errors.push(`Score must be between 0 and ${maxPoints}`);
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    /**
     * Generate CSV data for exam results
     */
    generateCSVData(examInfo, results) {
        const csvData = [];
        
        // Add header information
        csvData.push(['Exam Results Export']);
        csvData.push(['']);
        csvData.push(['Exam Title:', examInfo.title || 'N/A']);
        csvData.push(['Subject:', examInfo.subject || 'N/A']);
        csvData.push(['Date:', examInfo.date ? new Date(examInfo.date).toLocaleDateString() : 'N/A']);
        csvData.push(['Total Students:', results.length]);
        
        // Calculate statistics
        const stats = this.calculateStatistics(results);
        
        csvData.push(['Average Score:', stats.averageScore.toFixed(2) + '%']);
        csvData.push(['Highest Score:', stats.highestScore.toFixed(2) + '%']);
        csvData.push(['Lowest Score:', stats.lowestScore.toFixed(2) + '%']);
        csvData.push(['Pass Rate (≥75%):', stats.passRate.toFixed(1) + '%']);
        csvData.push(['Export Date:', new Date().toLocaleString()]);
        csvData.push(['']);
        
        // Add table headers
        csvData.push(['Rank', 'Student ID', 'Student Name', 'Score (%)', 'Grade', 'Status', 'Completion Date']);
        
        // Sort results by score
        const sortedResults = this.sortResultsByScore(results);
        
        // Add student data
        sortedResults.forEach((student, index) => {
            const score = parseFloat(student.score) || 0;
            const grade = this.getGrade(score);
            const status = this.getStatus(score);
            
            // Handle student name
            let studentName = 'Unknown Student';
            if (student.name && student.name !== 'N/A' && student.name !== 'Unknown Student') {
                studentName = student.name;
            } else if (student.student_name && student.student_name !== 'N/A') {
                studentName = student.student_name;
            } else if (student.full_name && student.full_name !== 'N/A') {
                studentName = student.full_name;
            }
            
            // Handle completion date
            let completionDate = 'Not Available';
            if (student.completed_at && student.completed_at !== 'N/A') {
                try {
                    completionDate = new Date(student.completed_at).toLocaleString();
                } catch (e) {
                    completionDate = student.completed_at;
                }
            } else if (student.end_time && student.end_time !== 'N/A') {
                try {
                    completionDate = new Date(student.end_time).toLocaleString();
                } catch (e) {
                    completionDate = student.end_time;
                }
            }
            
            // Handle student ID
            let studentId = 'Unknown ID';
            if (student.student_id && student.student_id !== 'N/A') {
                studentId = student.student_id;
            } else if (student.school_id && student.school_id !== 'N/A') {
                studentId = student.school_id;
            } else if (student.user_id && student.user_id !== 'N/A') {
                studentId = student.user_id;
            }
            
            csvData.push([
                index + 1,
                studentId,
                studentName,
                score.toFixed(2),
                grade,
                status,
                completionDate
            ]);
        });
        
        return csvData;
    }

    /**
     * Convert CSV data to CSV string
     */
    csvDataToString(csvData) {
        return csvData.map(row => 
            row.map(cell => {
                const cellStr = String(cell || '');
                if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                    return '"' + cellStr.replace(/"/g, '""') + '"';
                }
                return cellStr;
            }).join(',')
        ).join('\n');
    }

    /**
     * Generate CSV filename
     */
    generateCSVFilename(examInfo) {
        const examTitle = (examInfo.title || 'Exam').replace(/[^a-zA-Z0-9]/g, '_');
        const subject = (examInfo.subject || 'Subject').replace(/[^a-zA-Z0-9]/g, '_');
        const dateStr = examInfo.date ? new Date(examInfo.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
        return `${subject}_${examTitle}_Results_${dateStr}.csv`;
    }
}
