<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ios-blue: #007AFF;
            --ios-blue-light: #5AC8FA;
            --ios-blue-dark: #0051D5;
            --ios-gray: #F2F2F7;
            --ios-gray-2: #E5E5EA;
            --ios-gray-3: #D1D1D6;
            --ios-text: #1C1C1E;
            --ios-text-secondary: #3A3A3C;
        }
        
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        body { 
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #F0F4FF 0%, #E8F2FF 50%, #F0F8FF 100%);
            min-height: 100vh;
            color: var(--ios-text);
        }
        
        .ios-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 122, 255, 0.08), 0 2px 16px rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        .ios-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 60px rgba(0, 122, 255, 0.15), 0 8px 32px rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 122, 255, 0.2);
        }
        
        .ios-button {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .ios-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0, 122, 255, 0.4);
        }
        
        .header-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 50%, var(--ios-blue-dark) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .header-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.15"/><circle cx="20" cy="80" r="0.5" fill="white" opacity="0.15"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.4;
        }
        
        .loading-spinner {
            border: 3px solid rgba(0, 122, 255, 0.2);
            border-top-color: var(--ios-blue);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
        }
        
        .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .exam-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }
        
        .exam-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--ios-blue), var(--ios-blue-light));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .exam-card:hover::before {
            transform: scaleX(1);
        }
        
        .exam-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 48px rgba(0, 122, 255, 0.12);
            border-color: rgba(0, 122, 255, 0.2);
        }
        
        .subject-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 255, 0.95) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .subject-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(0, 122, 255, 0.1) 0%, transparent 70%);
            transform: translate(30px, -30px);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8 relative">
        <div class="container mx-auto px-8 relative z-10">
            <div class="flex justify-between items-center">
                <div class="fade-in">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mr-4">
                            <i class="fas fa-chart-bar text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold mb-2 tracking-tight">
                                Exam Results
                            </h1>
                            <p class="text-xl opacity-90 font-medium">
                                View and analyze student performance
                            </p>
                        </div>
                    </div>
                </div>
                <div class="fade-in">
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/dashboard" 
                       class="bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white px-8 py-3 rounded-2xl hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-8 max-w-7xl">
        <!-- Exam Selection Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1">
                <div class="ios-card p-8 fade-in">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-list text-white text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Select Exam</h2>
                            <p class="text-gray-600 text-sm">Choose an exam to view results</p>
                        </div>
                    </div>
                    <div id="examsList" class="space-y-3">
                        <!-- Exams will be loaded here -->
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-2">
                <div id="resultsContainer" class="ios-card p-8 fade-in">
                    <!-- Results will be shown here -->
                    <div class="text-center py-16 text-gray-500">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-clipboard-list text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Select an Exam</h3>
                        <p class="text-gray-500">Choose an exam from the list to view student results and performance analytics</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Details Modal -->
    <div id="detailsModal" class="fixed inset-0 modal-backdrop hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="modal-content max-w-4xl w-full max-h-[90vh] overflow-hidden fade-in">
                <!-- Modal Header -->
                <div class="header-gradient p-8 text-white relative">
                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center">
                            <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mr-4">
                                <i class="fas fa-user-graduate text-white text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold" id="modalTitle">Student Details</h3>
                                <p class="text-white/80 text-lg" id="modalSubtitle">Exam Results Analysis</p>
                            </div>
                        </div>
                        <button onclick="closeDetailsModal()" class="w-12 h-12 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                            <i class="fas fa-times text-white text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div id="modalBody" class="p-8 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global state
        let currentExamId = null;
        let examsData = [];
        let resultsData = [];
        let currentResults = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadExams();
            setupModalListeners();
            
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
        });

        // Load exams
        async function loadExams() {
            try {
                const response = await fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/api/exams');
                const data = await response.json();
                
                if (data.success && data.exams) {
                    examsData = data.exams;
                    displayExams(data.exams);
                }
            } catch (error) {
                console.error('Error loading exams:', error);
                document.getElementById('examsList').innerHTML = 
                    '<p class="text-red-600 text-sm">Error loading exams</p>';
            }
        }

        // Display exams list
        function displayExams(exams) {
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
                                <button onclick="selectExam(${exam.id})" 
                                        class="exam-card w-full text-left p-4 transition-all ${currentExamId === exam.id ? 'border-blue-300 bg-blue-50' : 'hover:bg-gray-50'}">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-gray-800 mb-1">${exam.title}</div>
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <i class="fas fa-users mr-1"></i>
                                                ${exam.students || 0} students
                                                ${exam.date ? `<span class="mx-2">•</span><i class="fas fa-calendar mr-1"></i>${new Date(exam.date).toLocaleDateString()}` : ''}
                                            </div>
                                        </div>
                                        ${currentExamId === exam.id ? '<i class="fas fa-check-circle text-blue-600"></i>' : '<i class="fas fa-chevron-right text-gray-400"></i>'}
                                    </div>
                                </button>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // Select exam and load results
        async function selectExam(examId) {
            currentExamId = examId;
            displayExams(examsData); // Refresh to show selection
            
            const container = document.getElementById('resultsContainer');
            container.innerHTML = `
                <div class="text-center py-8">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading results...</p>
                </div>
            `;

            try {
                const response = await fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/api/exam/${examId}/results`);
                const data = await response.json();
                
                if (data.success && data.results) {
                    resultsData = data.results;
                    currentResults = data.results; // Store for export functionality
                    displayResults(data.results);
                } else {
                    currentResults = []; // Clear results when no data
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

        // Display results
        function displayResults(results) {
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
                            <button onclick="exportExamResults()" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-2 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 inline-flex items-center">
                                <i class="fas fa-download mr-2"></i>Export CSV
                            </button>
                            <div class="flex space-x-4">
                            <div class="text-center p-3 bg-blue-50 rounded-xl">
                                <div class="text-lg font-bold text-blue-600">${totalStudents}</div>
                                <div class="text-xs text-gray-600">Students</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-xl">
                                <div class="text-lg font-bold text-green-600">${averageScore.toFixed(1)}%</div>
                                <div class="text-xs text-gray-600">Average</div>
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
                            const grade = getGrade(score);
                            const gradeColor = getGradeColor(score);
                            const status = getStatus(score);
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
                                                    ${score.toFixed(1)}%
                                                </div>
                                                <div class="text-xs text-gray-500">Score</div>
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
                                            <button onclick="viewDetails(${student.id})" 
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

        // View student details - MODAL POPUP
        async function viewDetails(attemptId) {
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
                const response = await fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/api/student-exam-details/${attemptId}`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    displayStudentDetails(data.data);
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

        // Display student details in modal
        function displayStudentDetails(data) {
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            const modalBody = document.getElementById('modalBody');
            
            modalTitle.textContent = data.student_name || 'Student Details';
            modalSubtitle.textContent = data.exam_title || 'Exam Results Analysis';

            const score = parseFloat(data.score) || 0;
            const correctAnswers = data.correct_answers || 0;
            const totalQuestions = data.total_questions || 0;
            const grade = getGrade(score);
            const gradeColor = getGradeColor(score);

            const html = `
                <div class="fade-in">
                    <!-- Score Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="ios-card p-6 text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-percentage text-white text-xl"></i>
                            </div>
                            <div class="text-3xl font-bold text-blue-600 mb-2">${score.toFixed(1)}%</div>
                            <div class="text-sm text-gray-600">Overall Score</div>
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
                                <div class="font-semibold text-gray-800">${calculateTimeTaken(data)}</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-xl">
                                <div class="text-sm text-gray-600 mb-1">Performance</div>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold ${gradeColor}">${getStatus(score)}</span>
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
                            ${data.questions && data.questions.map((q, index) => `
                                <div class="exam-card p-6 ${q.is_correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'}">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full ${q.is_correct ? 'bg-green-500' : 'bg-red-500'} flex items-center justify-center mr-3">
                                                <span class="text-white font-bold">${index + 1}</span>
                                            </div>
                                            <div class="font-semibold text-gray-800">Question ${index + 1}</div>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold ${q.is_correct ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                                ${q.is_correct ? '✓ Correct' : '✗ Wrong'}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-gray-700 mb-4 p-4 bg-white rounded-xl">${q.question_text}</div>
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
                                </div>
                            `).join('') || '<div class="text-center py-8"><p class="text-gray-500">No question details available</p></div>'}
                        </div>
                    </div>
                </div>
            `;

            modalBody.innerHTML = html;
        }

        // Close modal
        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }

        // Setup modal listeners
        function setupModalListeners() {
            const modal = document.getElementById('detailsModal');
            
            // Close on backdrop click
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDetailsModal();
                }
            });
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeDetailsModal();
                }
            });
        }

        // Helper functions
        function calculateTimeTaken(data) {
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
        
        function getGrade(score) {
            if (score >= 95) return 'A+';
            if (score >= 90) return 'A';
            if (score >= 85) return 'B+';
            if (score >= 80) return 'B';
            if (score >= 75) return 'C+';
            if (score >= 70) return 'C';
            if (score >= 65) return 'D';
            return 'F';
        }

        function getGradeColor(score) {
            if (score >= 90) return 'bg-green-100 text-green-700';
            if (score >= 80) return 'bg-blue-100 text-blue-700';
            if (score >= 70) return 'bg-yellow-100 text-yellow-700';
            return 'bg-red-100 text-red-700';
        }

        function getStatus(score) {
            return score >= 75 ? 'Satisfactory' : 'Needs Improvement';
        }
        
        // Export exam results to CSV
        function exportExamResults() {
            if (!currentExamId || !currentResults || currentResults.length === 0) {
                alert('No exam results to export');
                return;
            }
            
            // Get exam information
            const examInfo = examsData.find(exam => exam.id == currentExamId);
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
            csvData.push(['Total Students:', currentResults.length]);
            
            // Calculate statistics
            const totalStudents = currentResults.length;
            const averageScore = currentResults.reduce((sum, student) => sum + (parseFloat(student.score) || 0), 0) / totalStudents;
            const highestScore = Math.max(...currentResults.map(s => parseFloat(s.score) || 0));
            const lowestScore = Math.min(...currentResults.map(s => parseFloat(s.score) || 0));
            const passRate = (currentResults.filter(s => (parseFloat(s.score) || 0) >= 75).length / totalStudents * 100);
            
            csvData.push(['Average Score:', averageScore.toFixed(2) + '%']);
            csvData.push(['Highest Score:', highestScore.toFixed(2) + '%']);
            csvData.push(['Lowest Score:', lowestScore.toFixed(2) + '%']);
            csvData.push(['Pass Rate (≥75%):', passRate.toFixed(1) + '%']);
            csvData.push(['Export Date:', new Date().toLocaleString()]);
            csvData.push(['']);
            
            // Add table headers
            csvData.push(['Rank', 'Student ID', 'Student Name', 'Score (%)', 'Grade', 'Status', 'Completion Date']);
            
            // Sort results by score (highest first)
            const sortedResults = [...currentResults].sort((a, b) => (parseFloat(b.score) || 0) - (parseFloat(a.score) || 0));
            
            // Add student data
            sortedResults.forEach((student, index) => {
                const score = parseFloat(student.score) || 0;
                const grade = getGrade(score);
                const status = getStatus(score);
                
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
                    score.toFixed(2),
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
                showToast('Exam results exported successfully!', 'success');
            } else {
                alert('Your browser does not support file downloads');
            }
        }
        
        // Toast notification function
        function showToast(message, type = 'info') {
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
    </script>
</body>
</html>
