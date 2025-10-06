<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/dashboard-shared.css" rel="stylesheet">
    <link href="/assets/css/faculty-shared.css" rel="stylesheet">
    <link href="/css/modern-animations.css" rel="stylesheet">
    <style>
        /* Faculty-specific styles */
        .subject-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }

        .subject-card::before {
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

        .subject-card:hover::before {
            transform: scaleX(1);
        }

        .subject-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 48px rgba(0, 122, 255, 0.12);
            border-color: rgba(0, 122, 255, 0.2);
        }

        .ios-badge {
            background: linear-gradient(135deg, #34C759 0%, #30D158 100%);
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(52, 199, 89, 0.3);
        }

        /* Modal animations */
        #subjectModal .relative.bg-gradient-to-br {
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform, opacity, filter;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-12 mb-12 relative">
        <div class="container mx-auto px-8 relative z-10">
            <div class="flex justify-between items-center">
                <div class="animate-fade-in-up">
                    <div class="flex items-center mb-4">
                        <div class="icon-container blue-gradient mr-4">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h1 class="sf-pro-display text-4xl font-bold mb-2 tracking-tight">
                                Faculty Dashboard
                            </h1>
                            <p class="text-xl opacity-90 font-medium">
                                Welcome back, <?= htmlspecialchars($faculty->getFullName() ?? 'Faculty') ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="animate-fade-in-up animate-delay-200">
                    <button onclick="openLogoutModal()" 
                       class="bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white px-8 py-3 rounded-2xl hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-8 max-w-7xl">
        <!-- Compact Dashboard Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mb-12">
            <!-- Main Stats & Quick Actions -->
            <div class="lg:col-span-3">
                <div class="ios-card p-8 animate-fade-in-up">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="sf-pro-display text-2xl font-bold text-gray-800">Teaching Overview</h2>
                            <p class="text-gray-600">Your academic dashboard</p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="/faculty/create-exam" 
                               class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>New Exam
                            </a>
                            <a href="/faculty/exam-results" 
                               class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 inline-block">
                                <i class="fas fa-chart-bar mr-2"></i>View Scores
                            </a>
                        </div>
                    </div>
                    
                    <!-- Compact Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-book text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-blue-600 mb-1"><?= count($assignments) ?></div>
                            <div class="text-sm text-gray-600">Subjects</div>
                        </div>
                        
                        <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-green-600 mb-1"><?= $studentStats['total_students'] ?? 0 ?></div>
                            <div class="text-sm text-gray-600">Students</div>
                        </div>
                        
                        <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-clipboard-check text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-purple-600 mb-1"><?= $examStats['total_exams'] ?? 0 ?></div>
                            <div class="text-sm text-gray-600">Exams</div>
                        </div>
                        
                        <div class="text-center p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-chart-line text-white"></i>
                            </div>
                            <div class="text-2xl font-bold text-orange-600 mb-1">
                                <?php 
                                $avgScore = $examStats['average_score'] ?? 0;
                                echo $avgScore > 0 ? number_format($avgScore, 1) . '%' : 'N/A';
                                ?>
                            </div>
                            <div class="text-sm text-gray-600">Avg Score</div>
                            <?php if ($avgScore > 0): ?>
                            <div class="mt-2">
                                <div class="progress-bar">
                                    <div class="progress-fill <?= $avgScore >= 90 ? 'progress-excellent' : ($avgScore >= 80 ? 'progress-good' : ($avgScore >= 70 ? 'progress-fair' : 'progress-poor')) ?>" style="width: <?= $avgScore ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access Sidebar -->
            <div class="ios-card p-8 animate-fade-in-up animate-delay-100">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-rocket text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="sf-pro-display text-xl font-bold text-gray-800">Quick Access</h2>
                        <p class="text-sm text-gray-600">Frequently used tools</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <a href="/faculty/exams" 
                       class="flex items-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-blue-50 hover:to-blue-100 rounded-2xl transition-all duration-200 group">
                        <i class="fas fa-list text-gray-600 group-hover:text-blue-600 mr-3"></i>
                        <span class="font-medium text-gray-700 group-hover:text-blue-700">All Exams</span>
                    </a>
                    
                    <a href="/faculty/students" 
                       class="flex items-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-green-50 hover:to-green-100 rounded-2xl transition-all duration-200 group">
                        <i class="fas fa-users text-gray-600 group-hover:text-green-600 mr-3"></i>
                        <span class="font-medium text-gray-700 group-hover:text-green-700">Students</span>
                    </a>
                    
                    <button onclick="exportAllData()" 
                            class="w-full flex items-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-purple-50 hover:to-purple-100 rounded-2xl transition-all duration-200 group">
                        <i class="fas fa-download text-gray-600 group-hover:text-purple-600 mr-3"></i>
                        <span class="font-medium text-gray-700 group-hover:text-purple-700">Export Data</span>
                    </button>
                </div>
            </div>
        </div>


        <!-- My Subjects - Streamlined -->
        <div class="ios-card p-8 mb-12 animate-fade-in-up animate-delay-200">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-book text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="sf-pro-display text-2xl font-bold text-gray-800">My Subjects</h2>
                        <p class="text-gray-600">Teaching assignments & management</p>
                    </div>
                </div>
                <div class="text-sm text-gray-500">
                    <?= count($assignments) ?> subjects assigned
                </div>
            </div>
            <?php if (!empty($assignments)): ?>
                <?php 
                // Assignments should be pre-grouped by controller/service
                // This is a temporary fallback - move to service layer
                $assignmentsByYear = $assignmentsByYear ?? [];
                if (empty($assignmentsByYear)) {
                    $assignmentsByYear = [];
                    foreach ($assignments as $assignment) {
                        $yearLevel = $assignment->getYearLevel() ?? 'Unassigned';
                        $assignmentsByYear[$yearLevel][] = $assignment;
                    }
                    uksort($assignmentsByYear, function($a, $b) {
                        $order = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'Unassigned'];
                        return (array_search($a, $order) ?: 999) - (array_search($b, $order) ?: 999);
                    });
                }
                ?>
                
                <?php foreach ($assignmentsByYear as $yearLevel => $yearAssignments): ?>
                    <!-- Year Level Section -->
                    <div class="mb-10">
                        <!-- Year Level Header -->
                        <div class="subject-card p-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-4">
                                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="sf-pro-display text-xl font-bold text-gray-800"><?= htmlspecialchars($yearLevel) ?></h3>
                                        <p class="text-gray-600">My Teaching Assignments</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="bg-indigo-100 text-indigo-800 px-4 py-2 rounded-full">
                                        <span class="font-semibold"><?= count($yearAssignments) ?></span>
                                        <span class="text-sm">Subject<?= count($yearAssignments) !== 1 ? 's' : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Subjects Grid for this Year Level -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($yearAssignments as $assignment): ?>
                                <div class="subject-card p-6 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="sf-pro-display text-xl font-bold text-gray-900 mb-1">
                                                <?= htmlspecialchars($assignment->toArray()['subject_code'] ?? '') ?>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <?= htmlspecialchars($assignment->toArray()['subject_name'] ?? '') ?>
                                            </p>
                                        </div>
                                        <span class="ios-badge">
                                            Active
                                        </span>
                                    </div>
                                    <div class="space-y-3 mb-6">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-users mr-3 text-blue-500"></i>
                                            <span class="font-medium">Section <?= htmlspecialchars($assignment->getSection()) ?></span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-calendar-alt mr-3 text-green-500"></i>
                                            <span class="font-medium"><?= htmlspecialchars($assignment->getSemester()) ?></span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-school mr-3 text-purple-500"></i>
                                            <span class="font-medium">AY <?= htmlspecialchars($assignment->getAcademicYear()) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="/faculty/create-exam?subject_id=<?= $assignment->getSubjectId() ?>" 
                                           class="ios-button flex-1 text-center text-white font-semibold text-sm py-2">
                                            <i class="fas fa-plus mr-1"></i>
                                            Create Exam
                                        </a>
                                        <a href="/faculty/exam-results?subject=<?= $assignment->getSubjectId() ?>&code=<?= urlencode($assignment->toArray()['subject_code'] ?? '') ?>" 
                                                class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-3 py-2 rounded-xl font-semibold transition-all duration-200 text-sm inline-block text-center">
                                            <i class="fas fa-chart-bar mr-1"></i>
                                            Scores
                                        </a>
                                        <button onclick="showSubjectDetails(<?= htmlspecialchars(json_encode([
                                            'subject_code' => $assignment->toArray()['subject_code'] ?? '',
                                            'subject_name' => $assignment->toArray()['subject_name'] ?? '',
                                            'year_level' => $assignment->getYearLevel(),
                                            'section' => $assignment->getSection(),
                                            'semester' => $assignment->getSemester(),
                                            'academic_year' => $assignment->getAcademicYear(),
                                            'subject_id' => $assignment->getSubjectId()
                                        ])) ?>)" 
                                                class="bg-gray-100 text-gray-700 px-3 py-2 rounded-xl font-semibold hover:bg-gray-200 transition-colors text-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            Details
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-book-open text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Subjects Assigned</h3>
                        <p class="text-gray-500">You don't have any subjects assigned to you yet.</p>
                        <p class="text-gray-500">Please contact the administrator for subject assignments.</p>
                    </div>
                <?php endif; ?>
        </div>
    </div>

    <!-- Load shared dashboard JavaScript -->
    <script src="/assets/js/dashboard-shared.js"></script>
    
    <!-- Load Modern Modal Service -->
    <script src="/js/utils/ModernModalService.js"></script>
    
    <!-- Load faculty dashboard inline (has export functions) -->
    <script src="/js/faculty-dashboard-inline.js"></script>
    
    <!-- Load faculty dashboard controller (MVC) -->
    <script src="/js/controllers/faculty/FacultyDashboardController.js"></script>

    <!-- Subject Details Modal -->
    <div id="subjectModal" class="fixed inset-0 z-[100] hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/0 backdrop-blur-0 transition-all duration-700 ease-out" onclick="closeSubjectModal()"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-6">
            <div class="relative bg-gradient-to-br from-white via-white to-blue-50/20 rounded-3xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-hidden transform transition-all duration-700 ease-[cubic-bezier(0.34,1.56,0.64,1)] scale-75 opacity-0 translate-y-12 rotate-1" 
                 id="subjectModalContent"
                 style="box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.3);">
                
                <!-- Decorative background pattern -->
                <div class="absolute inset-0 rounded-3xl overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-3xl transform translate-x-12 -translate-y-12"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-400/10 to-blue-400/10 rounded-full blur-2xl transform -translate-x-8 translate-y-8"></div>
                </div>
                
                <!-- Header -->
                <div class="relative bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600 p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="relative w-16 h-16 mr-6">
                                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-white/10 rounded-2xl shadow-lg transform rotate-3"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-white/30 to-white/20 rounded-2xl flex items-center justify-center transform -rotate-3 transition-transform duration-500 hover:rotate-0">
                                    <i class="fas fa-book text-white text-2xl"></i>
                                </div>
                                <!-- Floating particles -->
                                <div class="absolute -top-1 -right-1 w-2 h-2 bg-white/60 rounded-full animate-ping"></div>
                                <div class="absolute -bottom-1 -left-1 w-1.5 h-1.5 bg-white/40 rounded-full animate-pulse"></div>
                            </div>
                            <div>
                                <h3 class="sf-pro-display text-3xl font-bold mb-2" id="modalSubjectTitle">
                                    Subject Details
                                </h3>
                                <p class="text-white/80 text-lg">Complete subject information & management</p>
                            </div>
                        </div>
                        <button onclick="closeSubjectModal()" 
                                class="w-12 h-12 rounded-2xl bg-white/10 hover:bg-white/20 active:bg-white/30 backdrop-blur-sm flex items-center justify-center transition-all duration-200 transform hover:scale-110 active:scale-95">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Content Area -->
                <div class="relative p-8 overflow-y-auto max-h-[calc(95vh-200px)]" id="modalSubjectContent">
                    <!-- Content will be populated by JavaScript -->
                    <div class="text-center py-12">
                        <div class="relative w-16 h-16 mx-auto mb-6">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl animate-pulse"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin text-white text-2xl"></i>
                            </div>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-800 mb-2">Loading Subject Details</h4>
                        <p class="text-gray-600">Please wait while we fetch the information...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Export Dashboard Modal -->
    <div id="exportDashboardModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="modal-content p-0 max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="header-gradient p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-download text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold">Export Dashboard</h3>
                            <p class="text-white/80">Select exams to export their results</p>
                        </div>
                    </div>
                    <button onclick="closeExportDashboard()" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-white"></i>
                    </button>
                </div>
            </div>
            
            <!-- Modal Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                <!-- Export Controls -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <button onclick="selectAllExams()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-check-double mr-2"></i>Select All
                        </button>
                        <button onclick="deselectAllExams()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Deselect All
                        </button>
                        <span id="selectedCount" class="text-gray-600 font-medium">0 exams selected</span>
                    </div>
                    <button onclick="exportSelectedExams()" id="exportSelectedBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-download mr-2"></i>Export Selected
                    </button>
                </div>
                
                <!-- Exams List -->
                <div id="exportExamsList" class="space-y-4">
                    <!-- Exams will be loaded here -->
                    <div class="text-center py-8">
                        <div class="loading-spinner mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading exams...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="modal-content p-8 max-w-md w-full mx-4">
            <div class="flex items-center mb-6">
                <div class="icon-container red-gradient mr-4">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div>
                    <h3 class="sf-pro-display text-xl font-bold text-gray-800">Confirm Logout</h3>
                    <p class="text-gray-500">Are you sure you want to logout?</p>
                </div>
            </div>
            <div class="flex space-x-4">
                <button onclick="closeLogoutModal()" class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmLogout()" class="flex-1 text-center text-white font-semibold py-3 rounded-xl transition-all duration-200 hover:scale-105" style="background: linear-gradient(135deg, #FF3B30 0%, #FF6B6B 100%);">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </button>
            </div>
        </div>
    </div>

</body>
</html>
