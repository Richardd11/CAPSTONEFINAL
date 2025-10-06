/**
 * FacultyExamResultsController - MVC Controller for Faculty Exam Results Page
 * Handles exam results display, student details, CSV export, and faculty score overrides
 * NO BUSINESS LOGIC CHANGED - Pure extraction from view
 */
class FacultyExamResultsController {
    constructor() {
        // Global state
        this.currentExamId = null;
        this.examsData = [];
        this.resultsData = [];
        this.currentResults = [];
        this.currentOverrideData = null;
        
        this.initialize();
    }

    /**
     * Initialize controller
     */
    initialize() {
        this.loadExams();
        this.setupModalListeners();
        this.setupOverrideModalListeners();
        
        // Check for URL parameters to filter by subject
        const urlParams = new URLSearchParams(window.location.search);
        const subjectId = urlParams.get('subject');
        const subjectCode = urlParams.get('code');
        
        if (subjectId && subjectCode) {
            // Update header to show subject filter
            const headerTitle = document.querySelector('.header-gradient h1');
            const headerSubtitle = document.querySelector('.header-gradient p');
            if (headerTitle && headerSubtitle) {
                headerTitle.textContent = `${subjectCode} - Exam Results`;
                headerSubtitle.textContent = `View and analyze student performance for ${subjectCode}`;
            }
        }
    }

    /**
     * Load exams
     */
    async loadExams() {
        try {
            const response = await fetch('/faculty/api/exams');
            const data = await response.json();
            
            if (data.success && data.exams) {
                this.examsData = data.exams;
                this.displayExams(data.exams);
            }
        } catch (error) {
            console.error('Error loading exams:', error);
            document.getElementById('examsList').innerHTML = 
                '<p class="text-red-600 text-sm">Error loading exams</p>';
        }
    }

    /**
     * Display exams list
     */
    displayExams(exams) {
        const container = document.getElementById('examsList');
        
        // Check for URL parameters to filter by subject
        const urlParams = new URLSearchParams(window.location.search);
        const subjectCode = urlParams.get('code');
        
        // Filter exams by subject if specified in URL
        let filteredExams = exams;
        if (subjectCode) {
            filteredExams = exams.filter(exam => 
                exam.subject && exam.subject.toLowerCase() === subjectCode.toLowerCase()
            );
        }
        
        if (!filteredExams || filteredExams.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-gray-500 text-sm">${subjectCode ? `No exams found for ${subjectCode}` : 'No exams found'}</p>
                </div>
            `;
            return;
        }

        // Group by subject
        const grouped = {};
        filteredExams.forEach(exam => {
            const subject = exam.subject || 'General';
            if (!grouped[subject]) grouped[subject] = [];
            grouped[subject].push(exam);
        });

        let html = '';
        Object.keys(grouped).sort().forEach(subject => {
            html += `
                <div class="mb-6">
                    <div class="subject-header p-4 mb-3 relative">
                        <div class="flex items-center relative z-10">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-3">
                                <i class="fas fa-book text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">${subject}</h3>
                                <p class="text-xs text-gray-600">${grouped[subject].length} exam${grouped[subject].length !== 1 ? 's' : ''}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        ${grouped[subject].map(exam => `
                            <button onclick="facultyExamResults.selectExam(${exam.id})" 
                                    class="exam-card w-full text-left p-4 transition-all ${this.currentExamId === exam.id ? 'border-blue-300 bg-blue-50' : 'hover:bg-gray-50'}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-800 mb-1">${exam.title}</div>
                                        <div class="text-xs text-gray-500 flex items-center">
                                            <i class="fas fa-users mr-1"></i>
                                            ${exam.students || 0} students
                                            ${exam.date ? `<span class="mx-2">•</span><i class="fas fa-calendar mr-1"></i>${new Date(exam.date).toLocaleDateString()}` : ''}
                                        </div>
                                    </div>
                                    ${this.currentExamId === exam.id ? '<i class="fas fa-check-circle text-blue-600"></i>' : '<i class="fas fa-chevron-right text-gray-400"></i>'}
                                </div>
                            </button>
                        `).join('')}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    /**
     * Select exam and load results
     */
    async selectExam(examId) {
        this.currentExamId = examId;
        this.displayExams(this.examsData); // Refresh to show selection
        
        const container = document.getElementById('resultsContainer');
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-600">Loading results...</p>
            </div>
        `;

        try {
            const response = await fetch(`/faculty/api/exam/${examId}/results`);
            const data = await response.json();
            
            if (data.success && data.results) {
                this.resultsData = data.results;
                this.currentResults = data.results; // Store for export functionality
                this.displayResults(data.results);
            } else {
                this.currentResults = []; // Clear results when no data
                container.innerHTML = `
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-users-slash text-4xl mb-4"></i>
                        <p>No results found for this exam</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading results:', error);
            container.innerHTML = `
                <div class="text-center py-12 text-red-600">
                    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                    <p>Error loading results</p>
                </div>
            `;
        }
    }

    /**
     * Display results
     */
    displayResults(results) {
        const container = document.getElementById('resultsContainer');
        
        if (!results || results.length === 0) {
            container.innerHTML = `
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-users-slash text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Results Yet</h3>
                    <p class="text-gray-500">No students have taken this exam yet</p>
                </div>
            `;
            return;
        }

        // Sort by score
        results.sort((a, b) => (b.score || 0) - (a.score || 0));
        
        // Calculate statistics
        const totalStudents = results.length;
        const averageScore = results.reduce((sum, student) => sum + (parseFloat(student.score) || 0), 0) / totalStudents;
        const highestScore = Math.max(...results.map(s => parseFloat(s.score) || 0));
        const passRate = (results.filter(s => (parseFloat(s.score) || 0) >= 75).length / totalStudents * 100);

        const html = `
            <div class="fade-in">
                <!-- Header with Statistics -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Student Results</h2>
                        <p class="text-gray-600">Performance analysis and detailed breakdown</p>
                    </div>
                    <div class="flex items-center space-x-6">
                        <button onclick="facultyExamResults.exportExamResults()" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 inline-flex items-center">
                            <i class="fas fa-download mr-2"></i>Export CSV
                        </button>
                        <div class="flex space-x-4">
                        <div class="text-center p-3 bg-blue-50 rounded-xl">
                            <div class="text-lg font-bold text-blue-600">${totalStudents}</div>
                            <div class="text-xs text-gray-600">Students</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-xl">
                            <div class="text-lg font-bold text-green-600">${Math.round((averageScore / 100) * (results[0]?.total_points || results[0]?.total_questions || 100))}/${results[0]?.total_points || results[0]?.total_questions || 100}</div>
                            <div class="text-xs text-gray-600">Average Points</div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-xl">
                            <div class="text-lg font-bold text-purple-600">${passRate.toFixed(0)}%</div>
                            <div class="text-xs text-gray-600">Pass Rate</div>
                        </div>
                        </div>
                    </div>
                </div>
                
                <!-- Results Grid -->
                <div class="space-y-3">
                    ${results.map((student, index) => {
                        const score = parseFloat(student.score) || 0;
                        const grade = this.getGrade(score);
                        const gradeColor = this.getGradeColor(score);
                        const status = this.getStatus(score);
                        const date = new Date(student.completed_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        });
                        
                        const rankIcon = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}`;
                        
                        return `
                            <div class="exam-card p-6 hover:shadow-lg transition-all">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Rank -->
                                        <div class="w-12 h-12 rounded-full ${index < 3 ? 'bg-gradient-to-br from-yellow-400 to-yellow-600' : 'bg-gray-100'} flex items-center justify-center font-bold ${index < 3 ? 'text-white' : 'text-gray-600'}">
                                            ${index < 3 ? rankIcon : index + 1}
                                        </div>
                                        
                                        <!-- Student Info -->
                                        <div>
                                            <div class="font-semibold text-gray-800 text-lg">${student.name || 'Unknown'}</div>
                                            <div class="text-sm text-gray-500">${student.student_id || 'N/A'} • Completed ${date}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <!-- Score -->
                                        <div class="text-center">
                                            <div class="text-2xl font-bold ${score >= 75 ? 'text-green-600' : score >= 50 ? 'text-yellow-600' : 'text-red-600'}">
                                                ${Math.round((score / 100) * (result.total_points || result.total_questions))}/${result.total_points || result.total_questions}
                                            </div>
                                            <div class="text-xs text-gray-500">Points</div>
                                        </div>
                                        
                                        <!-- Grade -->
                                        <div class="text-center">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold ${gradeColor}">
                                                ${grade}
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">Grade</div>
                                        </div>
                                        
                                        <!-- Status -->
                                        <div class="text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium ${score >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                                ${status}
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">Status</div>
                                        </div>
                                        
                                        <!-- Action -->
                                        <button onclick="facultyExamResults.viewDetails(${student.id})" 
                                                class="ios-button px-6 py-3 text-sm">
                                            <i class="fas fa-eye mr-2"></i>View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;

        container.innerHTML = html;
    }

    /**
     * View student details - MODAL POPUP
     */
    async viewDetails(attemptId) {
        const modal = document.getElementById('detailsModal');
        const modalBody = document.getElementById('modalBody');
        
        // Show modal with loading
        modal.classList.remove('hidden');
        modalBody.innerHTML = `
            <div class="text-center py-12">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-600">Loading details...</p>
            </div>
        `;

        try {
            const response = await fetch(`/faculty/api/student-exam-details/${attemptId}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                this.displayStudentDetails(data.data);
            } else {
                modalBody.innerHTML = `
                    <div class="text-center py-12 text-red-600">
                        <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                        <p>Error loading student details</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading details:', error);
            modalBody.innerHTML = `
                <div class="text-center py-12 text-red-600">
                    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                    <p>Error loading student details</p>
                </div>
            `;
        }
    }

    /**
     * Display student details in modal
     */
    displayStudentDetails(data) {
        const modalTitle = document.getElementById('modalTitle');
        const modalSubtitle = document.getElementById('modalSubtitle');
        const modalBody = document.getElementById('modalBody');
        
        modalTitle.textContent = data.student_name || 'Student Details';
        modalSubtitle.textContent = data.exam_title || 'Exam Results Analysis';

        const score = parseFloat(data.score) || 0;
        const correctAnswers = data.correct_answers || 0;
        const totalQuestions = data.total_questions || 0;
        const totalPoints = data.total_points || totalQuestions; // Use total_points if available, fallback to question count
        const pointsEarned = Math.round((score / 100) * totalPoints); // Calculate points from percentage
        const grade = this.getGrade(score);
        const gradeColor = this.getGradeColor(score);

        const html = `
            <div class="fade-in">
                <!-- Score Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="ios-card p-6 text-center">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-trophy text-white text-xl"></i>
                        </div>
                        <div class="text-3xl font-bold text-blue-600 mb-2">${pointsEarned}/${totalPoints}</div>
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

                <!-- Student Information -->
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
                            <div class="font-semibold text-gray-800">${this.calculateTimeTaken(data)}</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <div class="text-sm text-gray-600 mb-1">Performance</div>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold ${gradeColor}">${this.getStatus(score)}</span>
                        </div>
                    </div>
                </div>

                <!-- Question Analysis -->
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
                        ${data.questions && data.questions.map((q, index) => {
                            const isEssay = q.question_type === 'essay';
                            const hasAIGrading = q.ai_grading && q.ai_grading.graded_by_ai;
                            const isOverridden = q.faculty_override && q.faculty_override.overridden;
                            
                            return `
                            <div class="exam-card p-6 ${isEssay ? 'border-purple-200 bg-purple-50' : q.is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'}">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full ${isEssay ? 'bg-purple-500' : q.is_correct ? 'bg-green-500' : 'bg-red-500'} flex items-center justify-center mr-3">
                                            <span class="text-white font-bold">${index + 1}</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">Question ${index + 1}</div>
                                            ${isEssay ? '<div class="text-xs text-purple-600 font-medium">Essay Question</div>' : ''}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        ${isEssay ? `
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-purple-600">${q.score || 0}/${q.max_points || 10}</div>
                                                <div class="text-xs text-gray-500">Points</div>
                                            </div>
                                            ${hasAIGrading ? `
                                                <div class="flex items-center space-x-1 px-2 py-1 bg-blue-100 rounded-full">
                                                    <i class="fas fa-robot text-blue-600 text-xs"></i>
                                                    <span class="text-xs text-blue-600 font-medium">AI: ${q.ai_grading.confidence}%</span>
                                                </div>
                                            ` : ''}
                                            ${isOverridden ? `
                                                <div class="flex items-center space-x-1 px-2 py-1 bg-orange-100 rounded-full">
                                                    <i class="fas fa-user-edit text-orange-600 text-xs"></i>
                                                    <span class="text-xs text-orange-600 font-medium">Override</span>
                                                </div>
                                            ` : ''}
                                        ` : `
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold ${q.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                                ${q.is_correct ? '✓ Correct' : '✗ Wrong'}
                                            </span>
                                        `}
                                    </div>
                                </div>
                                
                                <div class="text-gray-700 mb-4 p-4 bg-white rounded-xl">${q.question_text}</div>
                                
                                ${isEssay ? `
                                    <!-- Essay Answer Display -->
                                    <div class="space-y-4">
                                        <div class="p-4 bg-white rounded-xl border">
                                            <div class="text-sm font-medium text-gray-600 mb-2">Student Essay:</div>
                                            <div class="text-gray-800 whitespace-pre-wrap leading-relaxed">
                                                ${q.student_answer || 'No essay submitted'}
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                Word count: ${q.student_answer ? q.student_answer.split(' ').length : 0} words
                                            </div>
                                        </div>
                                        
                                        ${hasAIGrading ? `
                                            <!-- AI Grading Details -->
                                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                                                <div class="flex items-center mb-3">
                                                    <i class="fas fa-robot text-blue-600 mr-2"></i>
                                                    <h5 class="font-semibold text-blue-800">AI Analysis</h5>
                                                    <div class="ml-auto flex items-center space-x-2">
                                                        <span class="text-sm text-blue-600">Confidence: ${q.ai_grading.confidence}%</span>
                                                        ${q.ai_grading.requires_manual_review ? `
                                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                                                                <i class="fas fa-flag mr-1"></i>Review Needed
                                                            </span>
                                                        ` : ''}
                                                    </div>
                                                </div>
                                                
                                                <!-- Criterion Breakdown -->
                                                ${q.ai_grading.criterion_scores ? `
                                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                                        ${Object.entries(q.ai_grading.criterion_scores).map(([criterion, data]) => `
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
                                                ` : ''}
                                                
                                                <!-- AI Feedback -->
                                                <div class="space-y-3">
                                                    <div class="bg-white rounded-lg p-3">
                                                        <div class="text-sm font-medium text-gray-700 mb-1">Overall Feedback:</div>
                                                        <div class="text-sm text-gray-600">${q.ai_grading.overall_feedback}</div>
                                                    </div>
                                                    
                                                    ${q.ai_grading.strengths && q.ai_grading.strengths.length > 0 ? `
                                                        <div class="bg-white rounded-lg p-3">
                                                            <div class="text-sm font-medium text-green-700 mb-1">Strengths:</div>
                                                            <ul class="text-sm text-gray-600 list-disc list-inside">
                                                                ${q.ai_grading.strengths.map(strength => `<li>${strength}</li>`).join('')}
                                                            </ul>
                                                        </div>
                                                    ` : ''}
                                                    
                                                    ${q.ai_grading.improvements && q.ai_grading.improvements.length > 0 ? `
                                                        <div class="bg-white rounded-lg p-3">
                                                            <div class="text-sm font-medium text-orange-700 mb-1">Areas for Improvement:</div>
                                                            <ul class="text-sm text-gray-600 list-disc list-inside">
                                                                ${q.ai_grading.improvements.map(improvement => `<li>${improvement}</li>`).join('')}
                                                            </ul>
                                                        </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        ` : ''}
                                        
                                        <!-- Faculty Override Section -->
                                        <div class="bg-gradient-to-r from-orange-50 to-yellow-50 rounded-xl p-4 border border-orange-200">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center">
                                                    <i class="fas fa-user-edit text-orange-600 mr-2"></i>
                                                    <h5 class="font-semibold text-orange-800">Faculty Override</h5>
                                                </div>
                                                ${!isOverridden ? `
                                                    <button onclick="console.log('Button clicked:', ${data.attempt_id}, ${q.question_id || 'undefined'}, ${q.score || 0}, ${q.max_points || 10}); facultyExamResults.showOverrideModal(${data.attempt_id}, ${q.question_id || (index + 1)}, ${q.score || 0}, ${q.max_points || 10})" 
                                                            class="px-3 py-1 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600 transition-colors">
                                                        <i class="fas fa-edit mr-1"></i>Override Score
                                                    </button>
                                                ` : `
                                                    <div class="text-sm text-orange-600">
                                                        <i class="fas fa-check-circle mr-1"></i>Score Overridden
                                                    </div>
                                                `}
                                            </div>
                                            
                                            ${isOverridden ? `
                                                <div class="bg-white rounded-lg p-3">
                                                    <div class="grid grid-cols-2 gap-4 mb-2">
                                                        <div>
                                                            <div class="text-xs text-gray-500">Original AI Score:</div>
                                                            <div class="font-semibold text-gray-700">${q.faculty_override.original_score}/${q.max_points}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs text-gray-500">Faculty Score:</div>
                                                            <div class="font-semibold text-orange-600">${q.faculty_override.new_score}/${q.max_points}</div>
                                                        </div>
                                                    </div>
                                                    <div class="text-sm text-gray-600">
                                                        <strong>Reason:</strong> ${q.faculty_override.reason}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Overridden by ${q.faculty_override.faculty_name} on ${new Date(q.faculty_override.overridden_at).toLocaleString()}
                                                    </div>
                                                </div>
                                            ` : `
                                                <div class="text-sm text-gray-600">
                                                    Current score: <strong>${q.score || 0}/${q.max_points || 10}</strong>
                                                    ${hasAIGrading ? ' (AI Generated)' : ' (Manual Grading Required)'}
                                                </div>
                                            `}
                                        </div>
                                    </div>
                                ` : `
                                    <!-- Regular Question Answer Display -->
                                    <div class="grid grid-cols-1 gap-4">
                                        <div class="p-3 bg-white rounded-xl">
                                            <div class="text-sm font-medium text-gray-600 mb-1">Student Answer:</div>
                                            <div class="font-semibold ${q.is_correct ? 'text-green-700' : 'text-red-700'}">
                                                ${q.student_answer || 'No answer provided'}
                                            </div>
                                        </div>
                                        ${!q.is_correct ? `
                                            <div class="p-3 bg-white rounded-xl">
                                                <div class="text-sm font-medium text-gray-600 mb-1">Correct Answer:</div>
                                                <div class="font-semibold text-green-700">${q.correct_answer}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                `}
                            </div>
                        `;
                        }).join('') || '<div class="text-center py-8"><p class="text-gray-500">No question details available</p></div>'}
                    </div>
                </div>
            </div>
        `;

        modalBody.innerHTML = html;
    }

    /**
     * Close details modal
     */
    closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    /**
     * Setup modal listeners
     */
    setupModalListeners() {
        const modal = document.getElementById('detailsModal');
        
        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeDetailsModal();
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                this.closeDetailsModal();
            }
        });
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
     * Export exam results to CSV
     */
    exportExamResults() {
        if (!this.currentExamId || !this.currentResults || this.currentResults.length === 0) {
            alert('No exam results to export');
            return;
        }
        
        // Get exam information
        const examInfo = this.examsData.find(exam => exam.id == this.currentExamId);
        if (!examInfo) {
            alert('Exam information not found');
            return;
        }
        
        // Prepare CSV data
        const csvData = [];
        
        // Add header information
        csvData.push(['Exam Results Export']);
        csvData.push(['']);
        csvData.push(['Exam Title:', examInfo.title || 'N/A']);
        csvData.push(['Subject:', examInfo.subject || 'N/A']);
        csvData.push(['Date:', examInfo.date ? new Date(examInfo.date).toLocaleDateString() : 'N/A']);
        csvData.push(['Total Students:', this.currentResults.length]);
        
        // Calculate statistics
        const totalStudents = this.currentResults.length;
        const averageScore = this.currentResults.reduce((sum, student) => sum + (parseFloat(student.score) || 0), 0) / totalStudents;
        const highestScore = Math.max(...this.currentResults.map(s => parseFloat(s.score) || 0));
        const lowestScore = Math.min(...this.currentResults.map(s => parseFloat(s.score) || 0));
        const passRate = (this.currentResults.filter(s => (parseFloat(s.score) || 0) >= 75).length / totalStudents * 100);
        
        // Calculate point-based statistics
        const sampleResult = this.currentResults[0];
        const totalPossiblePoints = sampleResult?.total_points || sampleResult?.total_questions || 100;
        const avgPoints = Math.round((averageScore / 100) * totalPossiblePoints);
        const highPoints = Math.round((highestScore / 100) * totalPossiblePoints);
        const lowPoints = Math.round((lowestScore / 100) * totalPossiblePoints);
        
        csvData.push(['Average Score:', `${avgPoints}/${totalPossiblePoints} points`]);
        csvData.push(['Highest Score:', `${highPoints}/${totalPossiblePoints} points`]);
        csvData.push(['Lowest Score:', `${lowPoints}/${totalPossiblePoints} points`]);
        csvData.push(['Pass Rate (≥75%):', passRate.toFixed(1) + '%']);
        csvData.push(['Export Date:', new Date().toLocaleString()]);
        csvData.push(['']);
        
        // Add table headers
        csvData.push(['Rank', 'Student ID', 'Student Name', 'Points Earned', 'Grade', 'Status', 'Completion Date']);
        
        // Sort results by score (highest first)
        const sortedResults = [...this.currentResults].sort((a, b) => (parseFloat(b.score) || 0) - (parseFloat(a.score) || 0));
        
        // Add student data
        sortedResults.forEach((student, index) => {
            const score = parseFloat(student.score) || 0;
            const grade = this.getGrade(score);
            const status = this.getStatus(score);
            
            // Better handling of student name (API returns 'name' field)
            let studentName = 'Unknown Student';
            if (student.name && student.name !== 'N/A' && student.name !== 'Unknown Student') {
                studentName = student.name;
            } else if (student.student_name && student.student_name !== 'N/A') {
                studentName = student.student_name;
            } else if (student.full_name && student.full_name !== 'N/A') {
                studentName = student.full_name;
            }
            
            // Better handling of completion date (API returns 'completed_at' field)
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
            } else if (student.completion_date && student.completion_date !== 'N/A') {
                try {
                    completionDate = new Date(student.completion_date).toLocaleString();
                } catch (e) {
                    completionDate = student.completion_date;
                }
            }
            
            // Better handling of student ID (API returns 'student_id' field)
            let studentId = 'Unknown ID';
            if (student.student_id && student.student_id !== 'N/A') {
                studentId = student.student_id;
            } else if (student.school_id && student.school_id !== 'N/A') {
                studentId = student.school_id;
            } else if (student.user_id && student.user_id !== 'N/A') {
                studentId = student.user_id;
            }
            
            csvData.push([
                index + 1, // Rank
                studentId,
                studentName,
                `${Math.round((score / 100) * (result.total_points || result.total_questions))}/${result.total_points || result.total_questions}`,
                grade,
                status,
                completionDate
            ]);
        });
        
        // Convert to CSV string
        const csvContent = csvData.map(row => 
            row.map(cell => {
                // Escape quotes and wrap in quotes if contains comma, quote, or newline
                const cellStr = String(cell || '');
                if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                    return '"' + cellStr.replace(/"/g, '""') + '"';
                }
                return cellStr;
            }).join(',')
        ).join('\n');
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        // Generate filename based on exam info
        const examTitle = (examInfo.title || 'Exam').replace(/[^a-zA-Z0-9]/g, '_');
        const subject = (examInfo.subject || 'Subject').replace(/[^a-zA-Z0-9]/g, '_');
        const dateStr = examInfo.date ? new Date(examInfo.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
        const filename = `${subject}_${examTitle}_Results_${dateStr}.csv`;
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
            
            // Show success message
            this.showToast('Exam results exported successfully!', 'success');
        } else {
            alert('Your browser does not support file downloads');
        }
    }
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-semibold transition-all duration-300 transform translate-x-full`;
        
        if (type === 'success') {
            toast.className += ' bg-green-500';
            toast.innerHTML = `<i class="fas fa-check mr-2"></i>${message}`;
        } else if (type === 'error') {
            toast.className += ' bg-red-500';
            toast.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
        } else {
            toast.className += ' bg-blue-500';
            toast.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
        }
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Animate out and remove
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (document.body.contains(toast)) {
                    document.body.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    /**
     * Show override modal
     */
    showOverrideModal(attemptId, questionId, currentScore, maxPoints) {
        console.log('Override Modal Data:', { attemptId, questionId, currentScore, maxPoints });
        
        this.currentOverrideData = { attemptId, questionId, currentScore, maxPoints };
        
        const modal = document.getElementById('overrideModal');
        const form = document.getElementById('overrideForm');
        
        if (!modal || !form) {
            console.error('Override modal elements not found');
            this.showToast('Override modal not available', 'error');
            return;
        }
        
        // Reset form
        form.reset();
        document.getElementById('currentScoreDisplay').textContent = `${currentScore}/${maxPoints}`;
        document.getElementById('newScore').max = maxPoints;
        document.getElementById('newScore').value = currentScore;
        
        // Show modal
        modal.classList.remove('hidden');
        document.getElementById('newScore').focus();
        
        console.log('Override modal opened successfully');
    }

    /**
     * Close override modal
     */
    closeOverrideModal() {
        document.getElementById('overrideModal').classList.add('hidden');
        this.currentOverrideData = null;
    }

    /**
     * Submit override
     */
    async submitOverride() {
        console.log('Submit override called');
        
        if (!this.currentOverrideData) {
            console.error('No override data available');
            this.showToast('No override data available', 'error');
            return;
        }
        
        const newScore = parseFloat(document.getElementById('newScore').value);
        const reason = document.getElementById('overrideReason').value.trim();
        
        console.log('Override data:', { newScore, reason, currentOverrideData: this.currentOverrideData });
        
        if (!reason) {
            this.showToast('Please provide a reason for the override', 'error');
            return;
        }
        
        if (newScore < 0 || newScore > this.currentOverrideData.maxPoints) {
            this.showToast(`Score must be between 0 and ${this.currentOverrideData.maxPoints}`, 'error');
            return;
        }
        
        const submitBtn = document.getElementById('submitOverride');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
            submitBtn.disabled = true;
            
            const requestData = {
                attempt_id: this.currentOverrideData.attemptId,
                question_id: this.currentOverrideData.questionId,
                new_score: newScore,
                reason: reason
            };
            
            console.log('Sending override request:', requestData);
            
            const response = await fetch(`/faculty/api/override-score`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                this.showToast('Score overridden successfully!', 'success');
                this.closeOverrideModal();
                
                // Refresh the modal content
                if (this.currentOverrideData.attemptId) {
                    console.log('Refreshing details for attempt:', this.currentOverrideData.attemptId);
                    this.viewDetails(this.currentOverrideData.attemptId);
                }
            } else {
                console.error('Override failed:', data);
                this.showToast(data.message || 'Failed to override score', 'error');
            }
            
        } catch (error) {
            console.error('Error overriding score:', error);
            this.showToast('An error occurred while overriding the score', 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    /**
     * Setup override modal listeners
     */
    setupOverrideModalListeners() {
        const modal = document.getElementById('overrideModal');
        
        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.closeOverrideModal();
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                this.closeOverrideModal();
            }
        });
    }
}

// Initialize controller when DOM is ready
let facultyExamResults;
document.addEventListener('DOMContentLoaded', () => {
    facultyExamResults = new FacultyExamResultsController();
});

// Global functions for onclick handlers (backward compatibility)
function selectExam(examId) {
    facultyExamResults.selectExam(examId);
}

function viewDetails(attemptId) {
    facultyExamResults.viewDetails(attemptId);
}

function closeDetailsModal() {
    facultyExamResults.closeDetailsModal();
}

function exportExamResults() {
    facultyExamResults.exportExamResults();
}

function showToast(message, type) {
    facultyExamResults.showToast(message, type);
}

// Override modal functions - exposed to window for inline onclick handlers
window.showOverrideModal = function(attemptId, questionId, currentScore, maxPoints) {
    facultyExamResults.showOverrideModal(attemptId, questionId, currentScore, maxPoints);
}

window.closeOverrideModal = function() {
    facultyExamResults.closeOverrideModal();
}

window.submitOverride = function() {
    facultyExamResults.submitOverride();
}
