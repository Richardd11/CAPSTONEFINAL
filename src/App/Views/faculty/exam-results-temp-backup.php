<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results & Student Scores - Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #007AFF;
            --primary-green: #34C759;
            --primary-red: #FF3B30;
            --primary-orange: #FF9500;
            --primary-purple: #AF52DE;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-blue);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="bg-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Exam Results & Scores</h1>
                        <p class="text-gray-600">View and analyze student performance</p>
                    </div>
                </div>
                <a href="/faculty/dashboard" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Students</p>
                        <p class="text-2xl font-bold text-gray-800" id="totalStudents">0</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Exams</p>
                        <p class="text-2xl font-bold text-gray-800" id="totalExams">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-check text-green-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Average Score</p>
                        <p class="text-2xl font-bold text-gray-800" id="averageScore">0%</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Highest Score</p>
                        <p class="text-2xl font-bold text-gray-800" id="highestScore">0%</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-trophy text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Selection -->
        <div class="glass-card p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-list-alt mr-2 text-purple-600"></i>Select Exam
                </h2>
                <button onclick="refreshExams()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
            
            <div id="examsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Exams will be loaded here -->
                <div class="col-span-full text-center py-8">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading exams...</p>
                </div>
            </div>
        </div>

        <!-- Student Scores Section -->
        <div id="scoresSection" class="glass-card p-6 hidden">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>
                    <span id="examTitle">Student Scores</span>
                </h2>
                <div class="flex space-x-2">
                    <button onclick="sortScores('name')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sort-alpha-down mr-1"></i>Name
                    </button>
                    <button onclick="sortScores('score')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sort-numeric-down mr-1"></i>Score
                    </button>
                    <button onclick="exportScores()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        <i class="fas fa-download mr-1"></i>Export
                    </button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Rank</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Student</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Score</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Grade</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scoresTableBody">
                        <!-- Scores will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentExamId = null;
        let allScores = [];
        let examsData = [];

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, initializing...');
            loadStatistics();
            loadExams();
        });

        // Load statistics
        function loadStatistics() {
            // These would normally come from PHP
            const stats = {
                totalStudents: <?= json_encode($studentStats['total_students'] ?? 0) ?>,
                totalExams: <?= json_encode($examStats['total_exams'] ?? 0) ?>,
                averageScore: <?= json_encode($examStats['average_score'] ?? 0) ?>,
                highestScore: <?= json_encode($examStats['highest_score'] ?? 0) ?>
            };
            
            document.getElementById('totalStudents').textContent = stats.totalStudents;
            document.getElementById('totalExams').textContent = stats.totalExams;
            document.getElementById('averageScore').textContent = stats.averageScore + '%';
            document.getElementById('highestScore').textContent = stats.highestScore + '%';
        }

        // Load exams with proper error handling
        function loadExams() {
            console.log('Loading exams...');
            const container = document.getElementById('examsContainer');
            
            // Show loading state
            container.innerHTML = `
                <div class="col-span-full text-center py-8">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-gray-600">Loading exams...</p>
                </div>
            `;
            
            // Set timeout for the request
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
            
            // Fetch exams from API
            fetch('/faculty/api/exams', {
                signal: controller.signal,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                clearTimeout(timeoutId);
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                
                if (data.success && data.exams) {
                    examsData = data.exams;
                    displayExams(data.exams);
                } else {
                    displayNoExams();
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('Error loading exams:', error);
                displayError(error.message);
            });
        }

        // Display exams
        function displayExams(exams) {
            const container = document.getElementById('examsContainer');
            
            if (!exams || exams.length === 0) {
                displayNoExams();
                return;
            }
            
            container.innerHTML = exams.map(exam => `
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow cursor-pointer fade-in" 
                     onclick="selectExam(${exam.id}, '${escapeHtml(exam.title)}')">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-800">${escapeHtml(exam.title)}</h3>
                        <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs">
                            ${exam.students || 0} students
                        </span>
                    </div>
                    <p class="text-gray-600 text-sm mb-2">${escapeHtml(exam.subject || 'No subject')}</p>
                    <p class="text-gray-500 text-xs">
                        <i class="fas fa-calendar mr-1"></i>
                        ${formatDate(exam.date)}
                    </p>
                </div>
            `).join('');
        }

        // Display no exams message
        function displayNoExams() {
            const container = document.getElementById('examsContainer');
            container.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Exams Found</h3>
                    <p class="text-gray-600 mb-4">You haven't created any exams yet.</p>
                    <a href="/faculty/create-exam" 
                       class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg inline-block transition-colors">
                        <i class="fas fa-plus mr-2"></i>Create Your First Exam
                    </a>
                </div>
            `;
        }

        // Display error message
        function displayError(message) {
            const container = document.getElementById('examsContainer');
            container.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Error Loading Exams</h3>
                    <p class="text-gray-600 mb-4">${escapeHtml(message)}</p>
                    <button onclick="loadExams()" 
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition-colors">
                        <i class="fas fa-redo mr-2"></i>Try Again
                    </button>
                    <div class="mt-6 text-left max-w-md mx-auto">
                        <h4 class="font-semibold text-gray-700 mb-2">Troubleshooting:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Check your internet connection</li>
                            <li>• Verify you're logged in as faculty</li>
                            <li>• Try refreshing the page</li>
                            <li>• Contact support if the issue persists</li>
                        </ul>
                    </div>
                </div>
            `;
        }

        // Select an exam
        function selectExam(examId, examTitle) {
            console.log('Selected exam:', examId, examTitle);
            currentExamId = examId;
            
            // Update UI
            document.getElementById('examTitle').textContent = examTitle;
            document.getElementById('scoresSection').classList.remove('hidden');
            
            // Scroll to scores section
            document.getElementById('scoresSection').scrollIntoView({ behavior: 'smooth' });
            
            // Load scores
            loadScores(examId);
        }

        // Load scores for selected exam
        function loadScores(examId) {
            const tbody = document.getElementById('scoresTableBody');
            
            // Show loading state
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-8">
                        <div class="loading-spinner mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading scores...</p>
                    </td>
                </tr>
            `;
            
            // Fetch scores from API
            fetch(`/faculty/api/exam/${examId}/results`)
                .then(response => response.json())
                .then(data => {
                    console.log('Scores data:', data);
                    
                    if (data.success && data.results) {
                        allScores = data.results;
                        displayScores(data.results);
                    } else {
                        displayNoScores();
                    }
                })
                .catch(error => {
                    console.error('Error loading scores:', error);
                    displayScoresError(error.message);
                });
        }

        // Display scores
        function displayScores(scores) {
            const tbody = document.getElementById('scoresTableBody');
            
            if (!scores || scores.length === 0) {
                displayNoScores();
                return;
            }
            
            // Sort by score descending
            scores.sort((a, b) => (b.score || 0) - (a.score || 0));
            
            tbody.innerHTML = scores.map((student, index) => {
                const score = parseFloat(student.score) || 0;
                const rank = index + 1;
                
                return `
                    <tr class="border-b hover:bg-gray-50 transition-colors fade-in">
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ${getRankColor(rank)} text-white font-semibold">
                                ${rank}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div>
                                <p class="font-semibold text-gray-800">${escapeHtml(student.name || 'Unknown')}</p>
                                <p class="text-sm text-gray-500">${escapeHtml(student.student_id || 'N/A')}</p>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center">
                                <span class="text-xl font-bold ${getScoreColor(score)}">${score}%</span>
                                <div class="ml-3 w-24 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full ${getProgressColor(score)}" style="width: ${score}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${getGradeColor(score)}">
                                ${getGrade(score)}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-3 py-1 rounded-full text-sm ${getStatusColor(score)}">
                                ${getStatus(score)}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-600">
                            ${formatDate(student.completed_at)}
                        </td>
                        <td class="py-3 px-4">
                            <button onclick="viewDetails(${student.id})" 
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Display no scores message
        function displayNoScores() {
            const tbody = document.getElementById('scoresTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="fas fa-users-slash text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Results Yet</h3>
                        <p class="text-gray-600">No students have taken this exam yet.</p>
                    </td>
                </tr>
            `;
        }

        // Display scores error
        function displayScoresError(message) {
            const tbody = document.getElementById('scoresTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <i class="fas fa-exclamation-circle text-6xl text-red-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Error Loading Scores</h3>
                        <p class="text-gray-600 mb-4">${escapeHtml(message)}</p>
                        <button onclick="loadScores(${currentExamId})" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-redo mr-2"></i>Try Again
                        </button>
                    </td>
                </tr>
            `;
        }

        // Utility functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }

        function getRankColor(rank) {
            if (rank === 1) return 'bg-yellow-500';
            if (rank === 2) return 'bg-gray-400';
            if (rank === 3) return 'bg-orange-600';
            return 'bg-purple-600';
        }

        function getScoreColor(score) {
            if (score >= 90) return 'text-green-600';
            if (score >= 80) return 'text-blue-600';
            if (score >= 70) return 'text-yellow-600';
            if (score >= 60) return 'text-orange-600';
            return 'text-red-600';
        }

        function getProgressColor(score) {
            if (score >= 90) return 'bg-green-500';
            if (score >= 80) return 'bg-blue-500';
            if (score >= 70) return 'bg-yellow-500';
            if (score >= 60) return 'bg-orange-500';
            return 'bg-red-500';
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
            if (score >= 90) return 'bg-green-100 text-green-800';
            if (score >= 80) return 'bg-blue-100 text-blue-800';
            if (score >= 70) return 'bg-yellow-100 text-yellow-800';
            if (score >= 60) return 'bg-orange-100 text-orange-800';
            return 'bg-red-100 text-red-800';
        }

        function getStatus(score) {
            if (score >= 90) return 'Excellent';
            if (score >= 80) return 'Good';
            if (score >= 70) return 'Satisfactory';
            if (score >= 60) return 'Pass';
            return 'Fail';
        }

        function getStatusColor(score) {
            if (score >= 90) return 'text-green-600';
            if (score >= 80) return 'text-blue-600';
            if (score >= 70) return 'text-yellow-600';
            if (score >= 60) return 'text-orange-600';
            return 'text-red-600';
        }

        // Sort scores
        function sortScores(by) {
            if (!allScores || allScores.length === 0) return;
            
            if (by === 'name') {
                allScores.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            } else if (by === 'score') {
                allScores.sort((a, b) => (b.score || 0) - (a.score || 0));
            }
            
            displayScores(allScores);
        }

        // Export scores
        function exportScores() {
            if (!allScores || allScores.length === 0) {
                alert('No scores to export');
                return;
            }
            
            // Create CSV content
            const headers = ['Rank', 'Student Name', 'Student ID', 'Score', 'Grade', 'Status', 'Date'];
            const rows = allScores.map((student, index) => {
                const score = parseFloat(student.score) || 0;
                return [
                    index + 1,
                    student.name || 'Unknown',
                    student.student_id || 'N/A',
                    score + '%',
                    getGrade(score),
                    getStatus(score),
                    formatDate(student.completed_at)
                ];
            });
            
            const csvContent = [
                headers.join(','),
                ...rows.map(row => row.join(','))
            ].join('\n');
            
            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `exam_scores_${currentExamId}_${Date.now()}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            // Show success message
            alert('Scores exported successfully!');
        }

        // View student details
        function viewDetails(attemptId) {
            // This would open a modal or navigate to a details page
            console.log('View details for attempt:', attemptId);
            alert('Details view coming soon!');
        }

        // Refresh exams
        function refreshExams() {
            loadExams();
        }
    </script>
</body>
</html>
