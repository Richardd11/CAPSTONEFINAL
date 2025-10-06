<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ios-blue: #007AFF;
            --ios-blue-light: #5AC8FA;
            --ios-blue-dark: #0051D5;
            --ios-gray: #F2F2F7;
            --ios-gray-dark: #8E8E93;
        }

        body {
            font-family: 'SF Pro Text', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #F0F4FF 0%, #E8F2FF 50%, #F0F8FF 100%);
            min-height: 100vh;
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
            transition: all 0.3s ease;
        }

        .ios-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 122, 255, 0.12), 0 4px 20px rgba(0, 0, 0, 0.06);
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 50%, var(--ios-blue-dark) 100%);
        }

        /* Ultra-Smooth Modal Animations */
        #examCompletedModal {
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        #examCompletedModal .relative {
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform, opacity, filter;
        }

        /* Modern button animations */
        #examCompletedModal button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow;
        }

        #examCompletedModal button:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        #examCompletedModal button:active {
            transform: translateY(0) scale(0.95);
            transition: all 0.1s ease;
        }

        /* Floating animation for particles */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-6px) rotate(180deg); }
        }

        #examCompletedModal .animate-ping {
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        #examCompletedModal .animate-pulse {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Gradient text animation */
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .bg-clip-text {
            background-size: 200% 200%;
            animation: gradient-shift 4s ease infinite;
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
        }

        .blue-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
        }

        .green-gradient {
            background: linear-gradient(135deg, #34C759 0%, #30D158 100%);
        }

        .orange-gradient {
            background: linear-gradient(135deg, #FF9500 0%, #FF9F0A 100%);
        }

        .purple-gradient {
            background: linear-gradient(135deg, #AF52DE 0%, #BF5AF2 100%);
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
            box-shadow: 0 16px 48px rgba(0, 122, 255, 0.12);
        }

        .exam-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            transition: all 0.3s ease;
            border-top: 4px solid var(--ios-blue);
        }

        .exam-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 122, 255, 0.12);
            border-top-color: var(--ios-blue-light);
        }

        .ios-button {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.3);
        }

        .ios-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.4);
        }

        .ios-badge {
            background: linear-gradient(135deg, #34C759 0%, #30D158 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .text-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }

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

        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15), 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .modal-icon-container {
            background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
            border: 2px solid rgba(239, 68, 68, 0.1);
        }

        .modal-button-cancel {
            background: rgba(107, 114, 128, 0.1);
            border: 1px solid rgba(107, 114, 128, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-button-cancel:hover {
            background: rgba(107, 114, 128, 0.15);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.15);
        }

        .modal-button-logout {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
        }

        .modal-button-logout:hover {
            background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }

        .modal-button-logout:disabled {
            opacity: 0.7;
            transform: none;
            cursor: not-allowed;
        }

        /* Modal animation classes */
        .scale-95 {
            transform: scale(0.95);
        }
        
        .scale-100 {
            transform: scale(1);
        }
        
        .opacity-0 {
            opacity: 0;
        }
        
        .opacity-100 {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-12 mb-8 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
        <div class="container mx-auto px-8 relative z-10">
            <div class="flex justify-between items-center">
                <div class="animate-fade-in-up">
                    <div class="flex items-center mb-4">
                        <div class="icon-container blue-gradient mr-4">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <h1 class="sf-pro-display text-4xl font-bold mb-2 tracking-tight">
                                Student Dashboard
                            </h1>
                            <p class="text-xl opacity-90 font-medium">
                                Welcome back, <?= htmlspecialchars($student->getFullName() ?? 'Student') ?>
                            </p>
                            <p class="text-lg opacity-80 font-medium">
                                <?= htmlspecialchars($student->getYearLevel()) ?> - Section <?= htmlspecialchars($student->getSection()) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="animate-fade-in-up animate-delay-200">
                    <button onclick="showLogoutModal()" 
                            class="bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white px-8 py-3 rounded-2xl hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-8 max-w-7xl">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            <div class="stats-card p-8 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="icon-container blue-gradient mr-6">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Available Exams</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= $studentStats['total_exams_available'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-8 animate-fade-in-up animate-delay-100">
                <div class="flex items-center">
                    <div class="icon-container green-gradient mr-6">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Completed</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= $studentStats['exams_completed'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-8 animate-fade-in-up animate-delay-200">
                <div class="flex items-center">
                    <div class="icon-container orange-gradient mr-6">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Pending</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= $studentStats['exams_pending'] ?></p>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Available Exams -->
        <div class="ios-card p-8 mb-12 animate-fade-in-up animate-delay-400">
            <div class="flex items-center mb-8">
                <div class="icon-container blue-gradient mr-4">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h2 class="sf-pro-display text-2xl font-bold text-gray-800">
                        Available Exams
                    </h2>
                    <p class="text-gray-500 font-medium">Take your assigned examinations</p>
                </div>
            </div>

            <?php if (empty($availableExams)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-clipboard-check text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Exams Available</h3>
                    <p class="text-gray-500 mb-2">You don't have any pending exams for your year level and section at the moment.</p>
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 inline-block">
                        <p class="text-blue-800 text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Your Details:</strong> <?= htmlspecialchars($student->getYearLevel()) ?> - Section <?= htmlspecialchars($student->getSection()) ?>
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($availableExams as $exam): ?>
                        <div class="exam-card p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="sf-pro-display text-xl font-bold text-gray-900 mb-1">
                                        <?= htmlspecialchars($exam['title']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($exam['subject_name'] ?? 'Subject') ?></p>
                                </div>
                                <span class="ios-badge">
                                    Available
                                </span>
                            </div>
                            
                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-clock mr-3 text-blue-500"></i>
                                    <span class="font-medium"><?= $exam['duration_minutes'] ?? 60 ?> minutes</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-list-ol mr-3 text-green-500"></i>
                                    <span class="font-medium"><?= $exam['total_questions'] ?? 0 ?> questions</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-star mr-3 text-purple-500"></i>
                                    <span class="font-medium"><?= $exam['total_points'] ?? 0 ?> points</span>
                                </div>
                            </div>
                            
                            <div class="flex space-x-3">
                                <a href="/student/exam/<?= $exam['id'] ?>" 
                                   class="ios-button flex-1 text-center text-white font-semibold">
                                    <i class="fas fa-play mr-2"></i>
                                    Start Exam
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Exam History -->
        <div class="ios-card p-8 animate-fade-in-up animate-delay-500">
            <div class="flex items-center mb-8">
                <div class="icon-container purple-gradient mr-4">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h2 class="sf-pro-display text-2xl font-bold text-gray-800">
                        Exam History
                    </h2>
                    <p class="text-gray-500 font-medium">Your completed examinations</p>
                </div>
            </div>

            <?php if (empty($examHistory)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-history text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Exam History</h3>
                    <p class="text-gray-500">You haven't completed any exams yet.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Exam</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Subject</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Date</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($examHistory as $history): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6">
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($history['exam_title']) ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-gray-600"><?= htmlspecialchars($history['subject_name']) ?></p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="text-gray-600">
                                            <?php 
                                            if (isset($history['completed_at']) && $history['completed_at']) {
                                                echo date('M d, Y', strtotime($history['completed_at']));
                                            } elseif (isset($history['created_at']) && $history['created_at']) {
                                                echo date('M d, Y', strtotime($history['created_at']));
                                            } else {
                                                echo 'Recently';
                                            }
                                            ?>
                                        </p>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="ios-badge">
                                            <?= ucfirst($history['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Exam Already Completed Modal -->
    <?php if (isset($_SESSION['show_exam_completed_modal'])): ?>
        <?php 
        $modalData = $_SESSION['show_exam_completed_modal'];
        unset($_SESSION['show_exam_completed_modal']);
        ?>
        <div id="examCompletedModal" class="fixed inset-0 bg-black/0 backdrop-blur-0 flex items-center justify-center z-[100] transition-all duration-700 ease-out">
            <div class="relative bg-gradient-to-br from-white via-white to-blue-50/30 rounded-3xl shadow-2xl max-w-lg w-full mx-6 p-0 transform transition-all duration-700 ease-[cubic-bezier(0.34,1.56,0.64,1)] scale-75 opacity-0 translate-y-12 rotate-1" 
                 style="box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.3);">
                
                <!-- Decorative background pattern -->
                <div class="absolute inset-0 rounded-3xl overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-2xl transform translate-x-8 -translate-y-8"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-green-400/10 to-blue-400/10 rounded-full blur-xl transform -translate-x-4 translate-y-4"></div>
                </div>
                
                <!-- Content -->
                <div class="relative p-8">
                    <!-- Header with animated icon -->
                    <div class="text-center mb-8">
                        <div class="relative w-20 h-20 mx-auto mb-6">
                            <div class="absolute inset-0 bg-gradient-to-br from-green-400 to-emerald-500 rounded-2xl shadow-lg transform rotate-3"></div>
                            <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center transform -rotate-3 transition-transform duration-500 hover:rotate-0">
                                <i class="fas fa-check-circle text-white text-3xl"></i>
                            </div>
                            <!-- Floating particles -->
                            <div class="absolute -top-2 -right-2 w-3 h-3 bg-green-400 rounded-full animate-ping"></div>
                            <div class="absolute -bottom-1 -left-1 w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        </div>
                        
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent mb-3">
                            Already Completed!
                        </h3>
                        <p class="text-slate-600 leading-relaxed mb-3">
                            Great job! You've already completed the exam 
                            <span class="font-semibold text-slate-800">"<?= htmlspecialchars($modalData['exam_title']) ?>"</span>
                        </p>
                        <div class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Completed: <?php 
                            if ($modalData['completed_date'] && $modalData['completed_date'] !== 'recently') {
                                $timestamp = strtotime($modalData['completed_date']);
                                echo $timestamp ? date('M d, Y', $timestamp) : 'Recently';
                            } else {
                                echo 'Recently';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="flex space-x-4">
                        <button onclick="closeExamCompletedModal()" 
                                class="flex-1 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 py-4 px-6 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 active:scale-95 hover:shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Dashboard
                        </button>
                        <button onclick="viewExamHistory()" 
                                class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 active:from-blue-700 active:to-blue-800 text-white py-4 px-6 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 active:scale-95 hover:shadow-lg shadow-blue-500/25">
                            <i class="fas fa-history mr-2"></i>
                            View History
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden">
        <!-- Backdrop -->
        <div class="modal-backdrop fixed inset-0" onclick="hideLogoutModal()"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="modal-content w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="logoutModalContent">
                <div class="p-8 text-center">
                    <!-- Icon -->
                    <div class="mx-auto mb-6">
                        <div class="w-20 h-20 modal-icon-container rounded-full flex items-center justify-center">
                            <i class="fas fa-sign-out-alt text-3xl text-red-600"></i>
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="sf-pro-display text-2xl font-bold text-gray-900 mb-3">
                        Confirm Logout
                    </h3>
                    
                    <!-- Message -->
                    <p class="text-gray-600 mb-8 leading-relaxed">
                        Are you sure you want to logout? You will need to login again to access your dashboard.
                    </p>
                    
                    <!-- Buttons -->
                    <div class="flex space-x-4">
                        <button onclick="hideLogoutModal()" 
                                class="flex-1 modal-button-cancel text-gray-700 px-6 py-3 rounded-xl font-semibold">
                            Cancel
                        </button>
                        <button onclick="performLogout()" 
                                class="flex-1 modal-button-logout text-white px-6 py-3 rounded-xl font-semibold">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide notifications after 5 seconds
        setTimeout(() => {
            const notifications = document.querySelectorAll('.fixed.top-4.right-4');
            notifications.forEach(notification => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            });
        }, 5000);

        // Modal functions
        function showLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const modalContent = document.getElementById('logoutModalContent');
            
            modal.classList.remove('hidden');
            
            // Animate modal in
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
        
        function hideLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const modalContent = document.getElementById('logoutModalContent');
            
            // Animate modal out
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }, 300);
        }
        
        function performLogout() {
            // Show loading state
            const logoutBtn = document.querySelector('#logoutModal button[onclick="performLogout()"]');
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging out...';
            logoutBtn.disabled = true;
            
            // Redirect to logout
            window.location.href = '/student/logout';
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideLogoutModal();
                closeExamCompletedModal();
            }
        });

        // Exam Completed Modal Functions
        function closeExamCompletedModal() {
            const modal = document.getElementById('examCompletedModal');
            if (modal) {
                const modalContent = modal.querySelector('.relative');
                
                if (modalContent) {
                    // Ultra-smooth exit animation with multiple effects
                    modalContent.style.transform = 'scale(0.8) translateY(30px) rotate(-2deg)';
                    modalContent.style.opacity = '0';
                    modalContent.style.filter = 'blur(4px)';
                }
                
                // Fade out backdrop with blur
                modal.style.background = 'rgba(0, 0, 0, 0)';
                modal.style.backdropFilter = 'blur(0px)';
                
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                    document.body.style.overflow = 'auto';
                }, 700);
            }
        }

        function viewExamHistory() {
            closeExamCompletedModal();
            // Scroll to exam history section
            setTimeout(() => {
                const examHistorySection = document.querySelector('.ios-card:last-of-type');
                if (examHistorySection) {
                    examHistorySection.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Add a subtle highlight effect
                    examHistorySection.style.boxShadow = '0 0 20px rgba(59, 130, 246, 0.3)';
                    setTimeout(() => {
                        examHistorySection.style.boxShadow = '';
                    }, 2000);
                }
            }, 300);
        }

        // Auto-show exam completed modal with ultra-smooth animation
        window.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('examCompletedModal');
            if (modal) {
                const modalContent = modal.querySelector('.relative');
                
                if (modalContent) {
                    // Prevent body scroll
                    document.body.style.overflow = 'hidden';
                    
                    // Ultra-smooth multi-stage animation sequence
                    setTimeout(() => {
                        // Stage 1: Backdrop fade with blur
                        modal.style.background = 'rgba(0, 0, 0, 0.4)';
                        modal.style.backdropFilter = 'blur(12px)';
                        
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
                } else {
                    console.error('Modal content not found');
                }
            }
        });
    </script>
</body>
</html>
