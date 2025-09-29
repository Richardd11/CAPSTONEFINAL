<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/assets/css/faculty-shared.css" rel="stylesheet">
    <style>
        :root {
            --ios-blue: #007AFF;
            --ios-blue-light: #5AC8FA;
            --ios-blue-dark: #0051D5;
            --ios-gray: #F2F2F7;
            --ios-gray-2: #E5E5EA;
            --ios-gray-3: #D1D1D6;
            --ios-gray-4: #C7C7CC;
            --ios-gray-5: #AEAEB2;
            --ios-gray-6: #8E8E93;
            --ios-text: #1C1C1E;
            --ios-text-secondary: #3A3A3C;
            --ios-text-tertiary: #48484A;
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

        .sf-pro-display {
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
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
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 122, 255, 0.15), 0 8px 32px rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 122, 255, 0.2);
        }

        .ios-button {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 16px;
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

        .ios-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 122, 255, 0.3);
        }

        /* Ultra-Smooth Modal Animations */
        #subjectModal .relative.bg-gradient-to-br {
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform, opacity, filter;
        }

        #subjectModal .fixed.inset-0 {
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Modern button animations for modal */
        #subjectModal button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow;
        }

        #subjectModal button:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        #subjectModal button:active {
            transform: translateY(0) scale(0.95);
            transition: all 0.1s ease;
        }

        /* Floating animation for particles */
        #subjectModal .animate-ping {
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        #subjectModal .animate-pulse {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .ios-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .ios-button:hover::before {
            left: 100%;
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

        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 122, 255, 0.1);
        }

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

        .action-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 250, 255, 0.95) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }

        .action-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(0, 122, 255, 0.1) 0%, transparent 70%);
            transform: translate(30px, -30px);
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 60px rgba(0, 122, 255, 0.15);
        }

        .action-card:hover::after {
            transform: translate(20px, -20px) scale(1.2);
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

        .text-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .icon-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
            border-radius: 16px;
        }

        .blue-gradient { background: linear-gradient(135deg, #007AFF 0%, #5AC8FA 100%); }
        .purple-gradient { background: linear-gradient(135deg, #AF52DE 0%, #BF5AF2 100%); }
        .green-gradient { background: linear-gradient(135deg, #34C759 0%, #30D158 100%); }
        .orange-gradient { background: linear-gradient(135deg, #FF9500 0%, #FF9F0A 100%); }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }

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

        .icon-container {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .icon-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            border-radius: inherit;
            opacity: 0.1;
        }

        .icon-container i {
            font-size: 24px;
            color: white;
            position: relative;
            z-index: 1;
        }

        .red-gradient { 
            background: linear-gradient(135deg, #FF3B30 0%, #FF6B6B 100%); 
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
                            <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam" 
                               class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105">
                                <i class="fas fa-plus mr-2"></i>New Exam
                            </a>
                            <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam-results" 
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
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exams" 
                       class="flex items-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 hover:from-blue-50 hover:to-blue-100 rounded-2xl transition-all duration-200 group">
                        <i class="fas fa-list text-gray-600 group-hover:text-blue-600 mr-3"></i>
                        <span class="font-medium text-gray-700 group-hover:text-blue-700">All Exams</span>
                    </a>
                    
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/students" 
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
                // Group assignments by year level
                $assignmentsByYear = [];
                foreach ($assignments as $assignment) {
                    $yearLevel = $assignment->getYearLevel() ?? 'Unassigned';
                    if (!isset($assignmentsByYear[$yearLevel])) {
                        $assignmentsByYear[$yearLevel] = [];
                    }
                    $assignmentsByYear[$yearLevel][] = $assignment;
                }
                
                // Sort year levels
                $yearOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'Unassigned'];
                uksort($assignmentsByYear, function($a, $b) use ($yearOrder) {
                    $aPos = array_search($a, $yearOrder);
                    $bPos = array_search($b, $yearOrder);
                    if ($aPos === false) $aPos = 999;
                    if ($bPos === false) $bPos = 999;
                    return $aPos - $bPos;
                });
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
                                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam?subject_id=<?= $assignment->getSubjectId() ?>" 
                                           class="ios-button flex-1 text-center text-white font-semibold text-sm py-2">
                                            <i class="fas fa-plus mr-1"></i>
                                            Create Exam
                                        </a>
                                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam-results?subject=<?= $assignment->getSubjectId() ?>&code=<?= urlencode($assignment->toArray()['subject_code'] ?? '') ?>" 
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

    <!-- Dashboard Scripts -->
    <script>
        // Initialize dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Dashboard loaded');
        });

        // Open export dashboard
        async function exportAllData() {
            // Show the export dashboard modal
            document.getElementById('exportDashboardModal').classList.remove('hidden');
            await loadExamsForExport();
        }
        
        // Export single exam data
        async function exportSingleExamData(exam) {
            try {
                // Fetch exam results
                const response = await fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/api/exam/${exam.id}/results`);
                const data = await response.json();
                
                if (!data.success || !data.results || data.results.length === 0) {
                    return false; // No results to export
                }
                
                const results = data.results;
                
                // Prepare CSV data
                const csvData = [];
                
                // Add header information
                csvData.push(['Exam Results Export']);
                csvData.push(['']);
                csvData.push(['Exam Title:', exam.title || 'N/A']);
                csvData.push(['Subject:', exam.subject || 'N/A']);
                csvData.push(['Date:', exam.date ? new Date(exam.date).toLocaleDateString() : 'N/A']);
                csvData.push(['Total Students:', results.length]);
                
                // Calculate statistics
                const totalStudents = results.length;
                const averageScore = results.reduce((sum, student) => sum + (parseFloat(student.score) || 0), 0) / totalStudents;
                const highestScore = Math.max(...results.map(s => parseFloat(s.score) || 0));
                const lowestScore = Math.min(...results.map(s => parseFloat(s.score) || 0));
                const passRate = (results.filter(s => (parseFloat(s.score) || 0) >= 75).length / totalStudents * 100);
                
                csvData.push(['Average Score:', averageScore.toFixed(2) + '%']);
                csvData.push(['Highest Score:', highestScore.toFixed(2) + '%']);
                csvData.push(['Lowest Score:', lowestScore.toFixed(2) + '%']);
                csvData.push(['Pass Rate (≥75%):', passRate.toFixed(1) + '%']);
                csvData.push(['Export Date:', new Date().toLocaleString()]);
                csvData.push(['']);
                
                // Add table headers
                csvData.push(['Rank', 'Student ID', 'Student Name', 'Score (%)', 'Grade', 'Status', 'Completion Date']);
                
                // Sort results by score (highest first)
                const sortedResults = [...results].sort((a, b) => (parseFloat(b.score) || 0) - (parseFloat(a.score) || 0));
                
                // Add student data with improved handling
                sortedResults.forEach((student, index) => {
                    const score = parseFloat(student.score) || 0;
                    const grade = getGradeForExport(score);
                    const status = score >= 75 ? 'Satisfactory' : 'Needs Improvement';
                    
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
                
                // Generate filename
                const examTitle = (exam.title || 'Exam').replace(/[^a-zA-Z0-9]/g, '_');
                const subject = (exam.subject || 'Subject').replace(/[^a-zA-Z0-9]/g, '_');
                const dateStr = exam.date ? new Date(exam.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
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
                }
                
                return true; // Successfully exported
                
            } catch (error) {
                console.error('Error exporting exam:', exam.title, error);
                throw error;
            }
        }
        
        // Grade calculation function for export
        function getGradeForExport(score) {
            if (score >= 95) return 'A+';
            if (score >= 90) return 'A';
            if (score >= 85) return 'B+';
            if (score >= 80) return 'B';
            if (score >= 75) return 'C+';
            if (score >= 70) return 'C';
            if (score >= 65) return 'D';
            return 'F';
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-2xl shadow-lg transform transition-all duration-300 translate-x-full`;
            
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            notification.className += ` ${bgColor} text-white`;
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'} mr-3"></i>
                    <span class="font-medium">${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            // Animate out and remove
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Enhanced subject modal functionality
        function showSubjectDetails(subjectData) {
            currentSubjectId = subjectData.subject_id;
            
            document.getElementById('modalSubjectTitle').textContent = 
                `${subjectData.subject_code} - ${subjectData.subject_name}`;
            
            const content = `
                <!-- Subject Header Card -->
                <div class="relative bg-gradient-to-br from-blue-50 via-white to-indigo-50 rounded-3xl p-8 mb-8 overflow-hidden">
                    <!-- Decorative background -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-2xl transform translate-x-8 -translate-y-8"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 to-blue-400/10 rounded-full blur-xl transform -translate-x-4 translate-y-4"></div>
                    
                    <div class="relative">
                        <div class="flex items-center mb-6">
                            <div class="relative w-16 h-16 mr-6">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg transform rotate-3"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center transform -rotate-3">
                                    <i class="fas fa-book text-white text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                                    ${subjectData.subject_code}
                                </h3>
                                <p class="text-xl text-gray-700 font-medium">${subjectData.subject_name}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Subject Information -->
                    <div class="relative bg-gradient-to-br from-white to-blue-50/30 rounded-2xl p-6 border border-blue-100/50 shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-info-circle text-white text-lg"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Subject Details</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-20">Code:</span>
                                <span class="text-lg font-bold text-gray-800">${subjectData.subject_code}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-20">Name:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.subject_name}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Information -->
                    <div class="relative bg-gradient-to-br from-white to-green-50/30 rounded-2xl p-6 border border-green-100/50 shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-graduation-cap text-white text-lg"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Class Details</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Year:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.year_level}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Section:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.section}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Semester:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.semester}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Year:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.academic_year}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="relative bg-gradient-to-br from-white to-purple-50/30 rounded-2xl p-6 border border-purple-100/50 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-bolt text-white text-lg"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800">Quick Actions</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam?subject_id=${subjectData.subject_id}" 
                           class="group relative bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-blue-500/25 text-center">
                            <i class="fas fa-plus mr-2"></i>Create Exam
                            <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        </a>
                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exams" 
                           class="group relative bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-green-500/25 text-center">
                            <i class="fas fa-list mr-2"></i>View All Exams
                            <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        </a>
                        <button onclick="viewSubjectStudents(${subjectData.subject_id})" 
                                class="group relative bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-purple-500/25">
                            <i class="fas fa-users mr-2"></i>View Students
                            <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        </button>
                    </div>
                </div>
            `;
            
            // Update content
            const contentArea = document.getElementById('modalSubjectContent');
            contentArea.innerHTML = content;
            
            // Show modal with ultra-smooth animation
            const modal = document.getElementById('subjectModal');
            const modalContent = modal.querySelector('.relative.bg-gradient-to-br');
            const backdrop = modal.querySelector('.fixed.inset-0.bg-black\\/0');
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Ultra-smooth multi-stage animation
            setTimeout(() => {
                // Stage 1: Backdrop fade with blur
                backdrop.style.background = 'rgba(0, 0, 0, 0.4)';
                backdrop.style.backdropFilter = 'blur(12px)';
                
                setTimeout(() => {
                    // Stage 2: Modal entrance with spring physics
                    modalContent.style.transform = 'scale(1.05) translateY(-8px) rotate(0deg)';
                    modalContent.style.opacity = '1';
                    modalContent.style.filter = 'blur(0px)';
                    
                    setTimeout(() => {
                        // Stage 3: Settle with micro-bounce
                        modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                        
                        setTimeout(() => {
                            // Stage 4: Final subtle pulse
                            modalContent.style.transform = 'scale(1.01) translateY(0) rotate(0deg)';
                            setTimeout(() => {
                                modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                            }, 150);
                        }, 200);
                    }, 300);
                }, 200);
            }, 100);
        }
    </script>

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

    <script>
        let currentSubjectId = null;

        function showSubjectDetails(subjectData) {
            currentSubjectId = subjectData.subject_id;
            
            document.getElementById('modalSubjectTitle').textContent = 
                `${subjectData.subject_code} - ${subjectData.subject_name}`;
            
            const content = `
                <!-- Subject Header Card -->
                <div class="relative bg-gradient-to-br from-blue-50 via-white to-indigo-50 rounded-3xl p-8 mb-8 overflow-hidden">
                    <!-- Decorative background -->
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-2xl transform translate-x-8 -translate-y-8"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 to-blue-400/10 rounded-full blur-xl transform -translate-x-4 translate-y-4"></div>
                    
                    <div class="relative">
                        <div class="flex items-center mb-6">
                            <div class="relative w-16 h-16 mr-6">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg transform rotate-3"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center transform -rotate-3">
                                    <i class="fas fa-book text-white text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                                    ${subjectData.subject_code}
                                </h3>
                                <p class="text-xl text-gray-700 font-medium">${subjectData.subject_name}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Subject Information -->
                    <div class="relative bg-gradient-to-br from-white to-blue-50/30 rounded-2xl p-6 border border-blue-100/50 shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-info-circle text-white text-lg"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Subject Details</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-20">Code:</span>
                                <span class="text-lg font-bold text-gray-800">${subjectData.subject_code}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-20">Name:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.subject_name}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Information -->
                    <div class="relative bg-gradient-to-br from-white to-green-50/30 rounded-2xl p-6 border border-green-100/50 shadow-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-graduation-cap text-white text-lg"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Class Details</h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Year:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.year_level}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Section:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.section}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Semester:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.semester}</span>
                            </div>
                            <div class="flex items-center p-3 bg-white/60 rounded-xl">
                                <span class="text-sm font-medium text-gray-600 w-24">Year:</span>
                                <span class="text-base font-semibold text-gray-800">${subjectData.academic_year}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="relative bg-gradient-to-br from-white to-purple-50/30 rounded-2xl p-6 border border-purple-100/50 shadow-lg">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-bolt text-white text-lg"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800">Quick Actions</h4>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam?subject_id=${subjectData.subject_id}" 
                           class="group relative bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-blue-500/25 text-center">
                            <i class="fas fa-plus mr-2"></i>Create Exam
                            <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        </a>
                        <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exams" 
                           class="group relative bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-green-500/25 text-center">
                            <i class="fas fa-list mr-2"></i>View All Exams
                            <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        </a>
                        <button onclick="viewSubjectStudents(${subjectData.subject_id})" 
                                class="group relative bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-6 py-4 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 hover:shadow-lg shadow-purple-500/25">
                            <i class="fas fa-users mr-2"></i>View Students
                            <div class="absolute inset-0 bg-white/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
                        </button>
                    </div>
                </div>
            `;
            
            // Update content
            const contentArea = document.getElementById('modalSubjectContent');
            contentArea.innerHTML = content;
            
            // Show modal with ultra-smooth animation
            const modal = document.getElementById('subjectModal');
            const modalContent = modal.querySelector('.relative.bg-gradient-to-br');
            const backdrop = modal.querySelector('.fixed.inset-0.bg-black\\/0');
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Ultra-smooth multi-stage animation
            setTimeout(() => {
                // Stage 1: Backdrop fade with blur
                backdrop.style.background = 'rgba(0, 0, 0, 0.4)';
                backdrop.style.backdropFilter = 'blur(12px)';
                
                setTimeout(() => {
                    // Stage 2: Modal entrance with spring physics
                    modalContent.style.transform = 'scale(1.05) translateY(-8px) rotate(0deg)';
                    modalContent.style.opacity = '1';
                    modalContent.style.filter = 'blur(0px)';
                    
                    setTimeout(() => {
                        // Stage 3: Settle with micro-bounce
                        modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                        
                        setTimeout(() => {
                            // Stage 4: Final subtle pulse
                            modalContent.style.transform = 'scale(1.01) translateY(0) rotate(0deg)';
                            setTimeout(() => {
                                modalContent.style.transform = 'scale(1) translateY(0) rotate(0deg)';
                            }, 150);
                        }, 200);
                    }, 300);
                }, 200);
            }, 100);
        }

        function closeSubjectModal() {
            const modal = document.getElementById('subjectModal');
            const modalContent = modal.querySelector('.relative.bg-gradient-to-br');
            const backdrop = modal.querySelector('.fixed.inset-0.bg-black\\/0');
            
            // Ultra-smooth exit animation with multiple effects
            modalContent.style.transform = 'scale(0.8) translateY(30px) rotate(-2deg)';
            modalContent.style.opacity = '0';
            modalContent.style.filter = 'blur(4px)';
            
            // Fade out backdrop with blur
            backdrop.style.background = 'rgba(0, 0, 0, 0)';
            backdrop.style.backdropFilter = 'blur(0px)';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                currentSubjectId = null;
                
                // Reset for next time
                modalContent.style.transform = 'scale(0.75) translateY(48px) rotate(1deg)';
                modalContent.style.opacity = '0';
                modalContent.style.filter = 'blur(0px)';
            }, 700);
        }

        function createExamForSubject() {
            if (currentSubjectId) {
                window.location.href = `<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam?subject_id=${currentSubjectId}`;
            }
        }

        function viewSubjectStudents(subjectId) {
            // Show loading state
            console.log('View students for subject:', subjectId);
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('subjectModal');
            if (e.target === modal) {
                closeSubjectModal();
            }
        });

        // Logout Modal Functions
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }
        
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        function confirmLogout() {
            // Disable the logout button to prevent double-clicks
            const logoutBtn = document.querySelector('#logoutModal button[onclick="confirmLogout()"]');
            const originalText = logoutBtn.innerHTML;
            logoutBtn.disabled = true;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging out...';
            
            fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/api/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/login';
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Logout failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Fallback to direct logout URL
                window.location.href = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/logout?confirm=true';
            });
        }
        
        // Navigation functions (scores modal removed - now using direct navigation)
        function viewSubjectScores(subjectId, subjectCode) {
            // Redirect to exam results page filtered by subject
            window.location.href = `<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam-results?subject=${subjectId}&code=${encodeURIComponent(subjectCode)}`;
        }
        
        // Export Dashboard Functions
        let availableExams = [];
        let selectedExams = new Set();
        
        async function loadExamsForExport() {
            const container = document.getElementById('exportExamsList');
            
            try {
                const response = await fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/api/exams');
                const data = await response.json();
                
                if (!data.success || !data.exams || data.exams.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-file-alt text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-500">No exams found</p>
                        </div>
                    `;
                    return;
                }
                
                availableExams = data.exams;
                displayExamsForExport(data.exams);
                
            } catch (error) {
                console.error('Error loading exams:', error);
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                        </div>
                        <p class="text-red-500">Error loading exams</p>
                    </div>
                `;
            }
        }
        
        function displayExamsForExport(exams) {
            const container = document.getElementById('exportExamsList');
            
            const html = exams.map(exam => `
                <div class="exam-export-card p-4 border border-gray-200 rounded-xl hover:border-blue-300 transition-colors" data-exam-id="${exam.id}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="checkbox" id="exam-${exam.id}" class="exam-checkbox w-5 h-5 text-blue-600 rounded mr-4" onchange="toggleExamSelection(${exam.id})">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="font-semibold text-gray-800">${exam.title || 'Untitled Exam'}</h4>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${exam.subject || 'No Subject'}</span>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">${exam.exam_type || 'exam'}</span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span><i class="fas fa-calendar mr-1"></i>${exam.date ? new Date(exam.date).toLocaleDateString() : 'No date'}</span>
                                    <span><i class="fas fa-clock mr-1"></i>${exam.time_limit || 60} minutes</span>
                                    <span id="student-count-${exam.id}"><i class="fas fa-users mr-1"></i>Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="previewExamResults(${exam.id})" class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Preview Results">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="exportSingleExam(${exam.id})" class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50 transition-colors" title="Export This Exam">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
            
            // Load student counts for each exam
            exams.forEach(exam => {
                loadExamStudentCount(exam.id);
            });
        }
        
        async function loadExamStudentCount(examId) {
            try {
                const response = await fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/api/exam/${examId}/results`);
                const data = await response.json();
                
                const count = data.success && data.results ? data.results.length : 0;
                const countElement = document.getElementById(`student-count-${examId}`);
                if (countElement) {
                    countElement.innerHTML = `<i class="fas fa-users mr-1"></i>${count} student${count !== 1 ? 's' : ''}`;
                    
                    // Disable checkbox if no results
                    const checkbox = document.getElementById(`exam-${examId}`);
                    if (checkbox && count === 0) {
                        checkbox.disabled = true;
                        checkbox.parentElement.parentElement.classList.add('opacity-50');
                    }
                }
            } catch (error) {
                console.error(`Error loading student count for exam ${examId}:`, error);
                const countElement = document.getElementById(`student-count-${examId}`);
                if (countElement) {
                    countElement.innerHTML = '<i class="fas fa-users mr-1"></i>Error';
                }
            }
        }
        
        function toggleExamSelection(examId) {
            const checkbox = document.getElementById(`exam-${examId}`);
            if (checkbox.checked) {
                selectedExams.add(examId);
            } else {
                selectedExams.delete(examId);
            }
            updateSelectionUI();
        }
        
        function selectAllExams() {
            const checkboxes = document.querySelectorAll('.exam-checkbox:not(:disabled)');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                selectedExams.add(parseInt(checkbox.id.replace('exam-', '')));
            });
            updateSelectionUI();
        }
        
        function deselectAllExams() {
            const checkboxes = document.querySelectorAll('.exam-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectedExams.clear();
            updateSelectionUI();
        }
        
        function updateSelectionUI() {
            const count = selectedExams.size;
            document.getElementById('selectedCount').textContent = `${count} exam${count !== 1 ? 's' : ''} selected`;
            
            const exportBtn = document.getElementById('exportSelectedBtn');
            exportBtn.disabled = count === 0;
        }
        
        async function exportSelectedExams() {
            if (selectedExams.size === 0) return;
            
            const button = document.getElementById('exportSelectedBtn');
            const originalContent = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Exporting...';
            button.disabled = true;
            
            let totalExported = 0;
            const failedExports = [];
            
            for (const examId of selectedExams) {
                const exam = availableExams.find(e => e.id == examId);
                if (!exam) continue;
                
                try {
                    const hasResults = await exportSingleExamData(exam);
                    if (hasResults) totalExported++;
                    
                    // Small delay between exports
                    await new Promise(resolve => setTimeout(resolve, 300));
                } catch (error) {
                    console.error(`Failed to export exam ${exam.title}:`, error);
                    failedExports.push(exam.title);
                }
            }
            
            // Show completion status
            button.innerHTML = '<i class="fas fa-check mr-2"></i>Exported!';
            
            let message = `Successfully exported ${totalExported} exam(s)`;
            if (failedExports.length > 0) {
                message += `. Failed: ${failedExports.length} exam(s)`;
            }
            
            showNotification(message, totalExported > 0 ? 'success' : 'warning');
            
            // Reset button after delay
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.disabled = selectedExams.size === 0;
            }, 3000);
        }
        
        async function exportSingleExam(examId) {
            const exam = availableExams.find(e => e.id == examId);
            if (!exam) return;
            
            try {
                const hasResults = await exportSingleExamData(exam);
                if (hasResults) {
                    showNotification(`Exported ${exam.title} successfully!`, 'success');
                } else {
                    showNotification(`No results found for ${exam.title}`, 'warning');
                }
            } catch (error) {
                showNotification(`Failed to export ${exam.title}`, 'error');
            }
        }
        
        function previewExamResults(examId) {
            // Navigate to exam results page for this specific exam
            window.open(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam-results`, '_blank');
        }
        
        function closeExportDashboard() {
            document.getElementById('exportDashboardModal').classList.add('hidden');
            selectedExams.clear();
            updateSelectionUI();
        }
        
        // Dashboard initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Faculty dashboard loaded successfully');
        });
    </script>


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
