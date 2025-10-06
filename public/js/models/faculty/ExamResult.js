/**
 * ExamResult Model
 * Represents exam result data structure
 */
class ExamResult {
    constructor(data) {
        this.id = data.id;
        this.name = data.name || data.student_name || 'Unknown';
        this.studentId = data.student_id || data.school_id || 'N/A';
        this.score = parseFloat(data.score) || 0;
        this.completedAt = data.completed_at;
        this.rawData = data;
    }

    getScore() {
        return this.score;
    }

    getName() {
        return this.name;
    }

    getStudentId() {
        return this.studentId;
    }

    getCompletedAt() {
        return this.completedAt;
    }

    getRawData() {
        return this.rawData;
    }
}

/**
 * Exam Model
 */
class Exam {
    constructor(data) {
        this.id = data.id;
        this.title = data.title || 'Untitled Exam';
        this.subject = data.subject || 'General';
        this.date = data.date;
        this.students = data.students || 0;
        this.rawData = data;
    }

    getId() {
        return this.id;
    }

    getTitle() {
        return this.title;
    }

    getSubject() {
        return this.subject;
    }

    getDate() {
        return this.date;
    }

    getStudentCount() {
        return this.students;
    }

    getRawData() {
        return this.rawData;
    }
}
