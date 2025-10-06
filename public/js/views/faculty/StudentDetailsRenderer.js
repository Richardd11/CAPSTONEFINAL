/**
 * StudentDetailsRenderer - Helper for complex student details rendering
 * Separated for maintainability
 */
class StudentDetailsRenderer {
    constructor(service) {
        this.service = service;
    }

    render(data) {
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalBody = document.getElementById('modalBody');
        
        modalTitle.textContent = data.student_name || 'Student Details';
        modalSubtitle.textContent = data.exam_title || 'Exam Results Analysis';

        const score = parseFloat(data.score) || 0;
        const correctAnswers = data.correct_answers || 0;
        const totalQuestions = data.total_questions || 0;
        const grade = this.service.getGrade(score);
        const gradeColor = this.service.getGradeColor(score);

        const totalPoints = data.total_points || totalQuestions;
        
        modalBody.innerHTML = `
            <div class="fade-in">
                ${this.renderSummaryCards(score, correctAnswers, totalQuestions, grade, gradeColor, totalPoints)}
                ${this.renderStudentInfo(data, score, gradeColor)}
                ${this.renderQuestionAnalysis(data)}
            </div>
        `;
    }

    renderSummaryCards(score, correctAnswers, totalQuestions, grade, gradeColor, totalPoints = null) {
        // Calculate points earned from percentage
        const examTotalPoints = totalPoints || totalQuestions;
        const pointsEarned = Math.round((score / 100) * examTotalPoints);
        
        return `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="ios-card p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-white text-xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-blue-600 mb-2">${pointsEarned}/${examTotalPoints}</div>
                    <div class="text-sm text-gray-600">Points Earned</div>
                </div>
                <div class="ios-card p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-white text-xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-green-600 mb-2">${correctAnswers}</div>
                    <div class="text-sm text-gray-600">Correct Answers</div>
                </div>
                <div class="ios-card p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-white text-xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-red-600 mb-2">${totalQuestions - correctAnswers}</div>
                    <div class="text-sm text-gray-600">Wrong Answers</div>
                </div>
                <div class="ios-card p-6 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-medal text-white text-xl"></i>
                    </div>
                    <div class="text-2xl font-bold text-purple-600 mb-2">${grade}</div>
                    <div class="text-sm text-gray-600">Final Grade</div>
                </div>
            </div>
        `;
    }

    renderStudentInfo(data, score, gradeColor) {
        return `
            <div class="ios-card p-6 mb-8">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-800">Student Information</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-sm text-gray-600 mb-1">Student ID</div>
                        <div class="font-semibold text-gray-800">${data.student_id || 'N/A'}</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-sm text-gray-600 mb-1">Time Taken</div>
                        <div class="font-semibold text-gray-800">${this.service.calculateTimeTaken(data)}</div>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <div class="text-sm text-gray-600 mb-1">Performance</div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold ${gradeColor}">${this.service.getStatus(score)}</span>
                    </div>
                </div>
            </div>
        `;
    }

    renderQuestionAnalysis(data) {
        if (!data.questions || data.questions.length === 0) {
            return '<div class="text-center py-8"><p class="text-gray-500">No question details available</p></div>';
        }

        return `
            <div class="ios-card p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-list-alt text-white"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-800">Question Analysis</h4>
                        <p class="text-gray-600">Detailed breakdown of each question</p>
                    </div>
                </div>
                <div class="space-y-4">
                    ${data.questions.map((q, index) => this.renderQuestion(q, index, data.attempt_id)).join('')}
                </div>
            </div>
        `;
    }

    renderQuestion(q, index, attemptId) {
        const isEssay = q.question_type === 'essay';
        
        if (isEssay) {
            return this.renderEssayQuestion(q, index, attemptId);
        } else {
            return this.renderRegularQuestion(q, index);
        }
    }

    renderEssayQuestion(q, index, attemptId) {
        const hasAIGrading = q.ai_grading && q.ai_grading.graded_by_ai;
        const isOverridden = q.faculty_override && q.faculty_override.overridden;
        
        return `
            <div class="exam-card p-6 border-purple-200 bg-purple-50">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center mr-3">
                            <span class="text-white font-bold">${index + 1}</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">Question ${index + 1}</div>
                            <div class="text-xs text-purple-600 font-medium">Essay Question</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="text-right">
                            <div class="text-lg font-bold text-purple-600">${q.score || 0}/${q.max_points || 10}</div>
                            <div class="text-xs text-gray-500">Points</div>
                        </div>
                        ${hasAIGrading ? `<div class="flex items-center space-x-1 px-2 py-1 bg-blue-100 rounded-full"><i class="fas fa-robot text-blue-600 text-xs"></i><span class="text-xs text-blue-600 font-medium">AI: ${q.ai_grading.confidence}%</span></div>` : ''}
                        ${isOverridden ? `<div class="flex items-center space-x-1 px-2 py-1 bg-orange-100 rounded-full"><i class="fas fa-user-edit text-orange-600 text-xs"></i><span class="text-xs text-orange-600 font-medium">Override</span></div>` : ''}
                    </div>
                </div>
                <div class="text-gray-700 mb-4 p-4 bg-white rounded-xl">${q.question_text}</div>
                <div class="space-y-4">
                    <div class="p-4 bg-white rounded-xl border">
                        <div class="text-sm font-medium text-gray-600 mb-2">Student Essay:</div>
                        <div class="text-gray-800 whitespace-pre-wrap leading-relaxed">${q.student_answer || 'No essay submitted'}</div>
                        <div class="mt-2 text-xs text-gray-500">Word count: ${q.student_answer ? q.student_answer.split(' ').length : 0} words</div>
                    </div>
                    ${hasAIGrading ? this.renderAIGradingDetails(q.ai_grading) : ''}
                    ${this.renderOverrideSection(q, attemptId, hasAIGrading, isOverridden)}
                </div>
            </div>
        `;
    }

    renderAIGradingDetails(aiGrading) {
        return `
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center mb-3">
                    <i class="fas fa-robot text-blue-600 mr-2"></i>
                    <h5 class="font-semibold text-blue-800">AI Analysis</h5>
                    <div class="ml-auto flex items-center space-x-2">
                        <span class="text-sm text-blue-600">Confidence: ${aiGrading.confidence}%</span>
                        ${aiGrading.requires_manual_review ? `<span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full"><i class="fas fa-flag mr-1"></i>Review Needed</span>` : ''}
                    </div>
                </div>
                ${aiGrading.criterion_scores ? this.renderCriterionScores(aiGrading.criterion_scores) : ''}
                <div class="space-y-3">
                    <div class="bg-white rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">Overall Feedback:</div>
                        <div class="text-sm text-gray-600">${aiGrading.overall_feedback}</div>
                    </div>
                    ${aiGrading.strengths && aiGrading.strengths.length > 0 ? `
                        <div class="bg-white rounded-lg p-3">
                            <div class="text-sm font-medium text-green-700 mb-1">Strengths:</div>
                            <ul class="text-sm text-gray-600 list-disc list-inside">
                                ${aiGrading.strengths.map(s => `<li>${s}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                    ${aiGrading.improvements && aiGrading.improvements.length > 0 ? `
                        <div class="bg-white rounded-lg p-3">
                            <div class="text-sm font-medium text-orange-700 mb-1">Areas for Improvement:</div>
                            <ul class="text-sm text-gray-600 list-disc list-inside">
                                ${aiGrading.improvements.map(i => `<li>${i}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    renderCriterionScores(criterionScores) {
        return `
            <div class="grid grid-cols-2 gap-3 mb-4">
                ${Object.entries(criterionScores).map(([criterion, data]) => `
                    <div class="bg-white rounded-lg p-3">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">${criterion.replace('_', ' ').toUpperCase()}</span>
                            <span class="text-sm font-bold text-blue-600">${data.score}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: ${data.score}%"></div>
                        </div>
                        <div class="text-xs text-gray-600 mt-1">${data.feedback}</div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    renderRegularQuestion(q, index) {
        return `
            <div class="exam-card p-6 ${q.is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full ${q.is_correct ? 'bg-green-500' : 'bg-red-500'} flex items-center justify-center mr-3">
                            <span class="text-white font-bold">${index + 1}</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">Question ${index + 1}</div>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold ${q.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                        ${q.is_correct ? '✓ Correct' : '✗ Wrong'}
                    </span>
                </div>
                <div class="text-gray-700 mb-4 p-4 bg-white rounded-xl">${q.question_text}</div>
                <div class="grid grid-cols-1 gap-4">
                    <div class="p-3 bg-white rounded-xl">
                        <div class="text-sm font-medium text-gray-600 mb-1">Student Answer:</div>
                        <div class="font-semibold ${q.is_correct ? 'text-green-700' : 'text-red-700'}">${q.student_answer || 'No answer provided'}</div>
                    </div>
                    ${!q.is_correct ? `<div class="p-3 bg-white rounded-xl"><div class="text-sm font-medium text-gray-600 mb-1">Correct Answer:</div><div class="font-semibold text-green-700">${q.correct_answer}</div></div>` : ''}
                </div>
            </div>
        `;
    }

    renderOverrideSection(q, attemptId, hasAIGrading, isOverridden) {
        return `
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl p-4 border border-orange-200">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <i class="fas fa-user-edit text-orange-600 mr-2"></i>
                        <h5 class="font-semibold text-orange-800">Faculty Override</h5>
                    </div>
                    ${!isOverridden ? `
                        <button onclick="facultyExamResults.showOverrideModal(${attemptId}, ${q.question_id || 0}, ${q.score || 0}, ${q.max_points || 10})" 
                                class="px-3 py-1 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="fas fa-edit mr-1"></i>Override Score
                        </button>
                    ` : `<div class="text-sm text-orange-600"><i class="fas fa-check-circle mr-1"></i>Score Overridden</div>`}
                </div>
                ${isOverridden ? `
                    <div class="bg-white rounded-lg p-3">
                        <div class="grid grid-cols-2 gap-4 mb-2">
                            <div><div class="text-xs text-gray-500">Original AI Score:</div><div class="font-semibold text-gray-700">${q.faculty_override.original_score}/${q.max_points}</div></div>
                            <div><div class="text-xs text-gray-500">Faculty Score:</div><div class="font-semibold text-orange-600">${q.faculty_override.new_score}/${q.max_points}</div></div>
                        </div>
                        <div class="text-sm text-gray-600"><strong>Reason:</strong> ${q.faculty_override.reason}</div>
                        <div class="text-xs text-gray-500 mt-1">Overridden by ${q.faculty_override.faculty_name} on ${new Date(q.faculty_override.overridden_at).toLocaleString()}</div>
                    </div>
                ` : `<div class="text-sm text-gray-600">Current score: <strong>${q.score || 0}/${q.max_points || 10}</strong>${hasAIGrading ? ' (AI Generated)' : ' (Manual Grading Required)'}</div>`}
            </div>
        `;
    }
}
