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
                    <a href="/faculty/dashboard" 
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

    <!-- MVC Architecture - Strict Separation of Concerns -->
    <!-- Model Layer -->
    <script src="/js/models/faculty/ExamResult.js"></script>
    
    <!-- Service Layer -->
    <script src="/js/services/faculty/FacultyExamResultsService.js"></script>
    
    <!-- View Layer -->
    <script src="/js/views/faculty/FacultyExamResultsView.js"></script>
    <script src="/js/views/faculty/StudentDetailsRenderer.js"></script>
    
    <!-- Controller Layer (Orchestration) -->
    <script src="/js/controllers/faculty/FacultyExamResultsController.refactored.js"></script>

    <!-- Faculty Override Modal -->
    <div id="overrideModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 transform transition-all duration-300">
            <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mr-3">
                            <i class="fas fa-user-edit text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Override AI Score</h3>
                            <p class="text-white text-opacity-90 text-sm">Adjust the AI-generated score</p>
                        </div>
                    </div>
                    <button onclick="closeOverrideModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <form id="overrideForm" onsubmit="event.preventDefault(); window.submitOverride();">
                    <!-- Current Score Display -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                        <div class="text-sm text-gray-600 mb-1">Current Score:</div>
                        <div class="text-2xl font-bold text-gray-800" id="currentScoreDisplay">0/10</div>
                    </div>
                    
                    <!-- New Score Input -->
                    <div class="mb-6">
                        <label for="newScore" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-edit mr-1 text-orange-500"></i>New Score
                        </label>
                        <input type="number" 
                               id="newScore" 
                               name="newScore" 
                               step="0.1" 
                               min="0" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-lg font-semibold">
                    </div>
                    
                    <!-- Reason Input -->
                    <div class="mb-6">
                        <label for="overrideReason" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-comment mr-1 text-orange-500"></i>Reason for Override <span class="text-red-500">*</span>
                        </label>
                        <textarea id="overrideReason" 
                                  name="overrideReason" 
                                  rows="3" 
                                  required
                                  placeholder="Explain why you're overriding the AI score..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"></textarea>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" 
                                onclick="closeOverrideModal()" 
                                class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors font-semibold">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" 
                                id="submitOverride"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl hover:from-orange-600 hover:to-red-600 transition-all duration-200 font-semibold">
                            <i class="fas fa-save mr-2"></i>Save Override
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
