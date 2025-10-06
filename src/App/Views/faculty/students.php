<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ios-blue: #007AFF;
            --ios-blue-light: #5AC8FA;
            --ios-blue-dark: #0051D5;
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
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 50%, var(--ios-blue-dark) 100%);
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
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
        }

        /* Ultra-Smooth Modal Animations */
        #studentModal .relative {
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform, opacity, filter;
        }

        #studentModal .fixed.inset-0 {
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Modern button animations */
        #studentModal button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow;
        }

        #studentModal button:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        #studentModal button:active {
            transform: translateY(0) scale(0.95);
            transition: all 0.1s ease;
        }

        /* Floating animation for particles */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-6px) rotate(180deg); }
        }

        #studentModal .animate-ping {
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        #studentModal .animate-pulse {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .student-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .student-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 122, 255, 0.12);
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
        }

        .student-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.1);
            border-color: rgba(0, 122, 255, 0.2);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8 relative">
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="/faculty/dashboard" 
                       class="mr-6 p-2 rounded-full hover:bg-white/20 transition-all duration-300 group">
                        <i class="fas fa-arrow-left text-xl group-hover:transform group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div>
                        <h1 class="sf-pro-display text-3xl font-bold mb-2 tracking-tight">
                            <i class="fas fa-users mr-3 text-white/90"></i>
                            My Students
                        </h1>
                        <p class="text-white/80 text-lg">Manage students in your assigned subjects</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 max-w-7xl">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats-card p-6">
                <div class="flex items-center">
                    <div class="icon-container mr-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Students</p>
                        <p class="sf-pro-display text-2xl font-bold text-blue-600"><?= $stats['total_students'] ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-6">
                <div class="flex items-center">
                    <div class="icon-container mr-4" style="background: linear-gradient(135deg, #34C759 0%, #30D158 100%);">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Year Levels</p>
                        <p class="sf-pro-display text-2xl font-bold text-green-600"><?= count($stats['by_year_level']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-6">
                <div class="flex items-center">
                    <div class="icon-container mr-4" style="background: linear-gradient(135deg, #AF52DE 0%, #BF5AF2 100%);">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Sections</p>
                        <p class="sf-pro-display text-2xl font-bold text-purple-600"><?= count($stats['by_section']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="ios-card p-8">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h2 class="sf-pro-display text-2xl font-bold text-gray-800">Student Roster</h2>
                    <p class="text-gray-500 font-medium">Students enrolled in your subjects</p>
                </div>
                <div class="flex space-x-3">
                    <button class="bg-blue-600 text-white px-6 py-3 rounded-xl hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-download mr-2"></i>Export List
                    </button>
                </div>
            </div>

            <?php if (empty($students)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-user-graduate text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Students Found</h3>
                    <p class="text-gray-500">No students are enrolled in your assigned subjects yet.</p>
                </div>
            <?php else: ?>
                <?php
                // Group students by year level and section
                $groupedStudents = [];
                foreach ($students as $student) {
                    $yearLevel = $student->getYearLevel();
                    $section = $student->getSection();
                    $key = $yearLevel . '_' . $section;
                    
                    if (!isset($groupedStudents[$key])) {
                        $groupedStudents[$key] = [
                            'year_level' => $yearLevel,
                            'section' => $section,
                            'students' => []
                        ];
                    }
                    $groupedStudents[$key]['students'][] = $student;
                }
                
                // Sort groups by year level then section
                ksort($groupedStudents);
                ?>
                
                <!-- Filter/Search Bar -->
                <div class="mb-6 flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="studentSearch" placeholder="Search students by name or ID..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <select id="yearFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Years</option>
                            <?php 
                            $years = array_unique(array_column($groupedStudents, 'year_level'));
                            sort($years);
                            foreach ($years as $year): 
                            ?>
                                <option value="<?= htmlspecialchars($year) ?>"><?= htmlspecialchars($year) ?> Year</option>
                            <?php endforeach; ?>
                        </select>
                        <select id="sectionFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Sections</option>
                            <?php 
                            $sections = array_unique(array_column($groupedStudents, 'section'));
                            sort($sections);
                            foreach ($sections as $section): 
                            ?>
                                <option value="<?= htmlspecialchars($section) ?>">Section <?= htmlspecialchars($section) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="space-y-6">
                    <?php foreach ($groupedStudents as $group): ?>
                        <div class="group-section bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-200" 
                             data-year="<?= htmlspecialchars($group['year_level']) ?>" 
                             data-section="<?= htmlspecialchars($group['section']) ?>">
                            
                            <!-- Collapsible Header -->
                            <div class="group-header bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 cursor-pointer hover:from-blue-100 hover:to-indigo-100 transition-colors"
                                 onclick="toggleGroup(this)">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                            <i class="fas fa-users text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="sf-pro-display text-lg font-bold text-gray-800">
                                                <?= htmlspecialchars($group['year_level']) ?><?= is_numeric($group['year_level']) ? (in_array(substr($group['year_level'], -1), ['1']) ? 'st' : (in_array(substr($group['year_level'], -1), ['2']) ? 'nd' : (in_array(substr($group['year_level'], -1), ['3']) ? 'rd' : 'th'))) : '' ?> Year - Section <?= htmlspecialchars($group['section']) ?>
                                            </h3>
                                            <p class="text-sm text-gray-600"><?= count($group['students']) ?> student<?= count($group['students']) !== 1 ? 's' : '' ?></p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <button class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-700 transition-colors font-medium"
                                                onclick="event.stopPropagation(); exportSection('<?= htmlspecialchars($group['year_level']) ?>', '<?= htmlspecialchars($group['section']) ?>')">
                                            <i class="fas fa-download mr-1"></i>Export
                                        </button>
                                        <i class="fas fa-chevron-down text-gray-400 transition-transform group-icon"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Students Table -->
                            <div class="group-content">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="text-left py-3 px-6 font-semibold text-gray-700 text-sm">Student ID</th>
                                                <th class="text-left py-3 px-6 font-semibold text-gray-700 text-sm">Full Name</th>
                                                <th class="text-left py-3 px-6 font-semibold text-gray-700 text-sm">Subject</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($group['students'] as $index => $student): ?>
                                                <tr class="student-row border-b border-gray-100 hover:bg-gray-50 transition-colors"
                                                    data-name="<?= strtolower(htmlspecialchars($student->getFullName())) ?>"
                                                    data-id="<?= strtolower(htmlspecialchars($student->getSchoolId())) ?>">
                                                    <td class="py-4 px-6">
                                                        <span class="font-mono text-sm bg-gray-100 px-3 py-1 rounded-lg">
                                                            <?= htmlspecialchars($student->getSchoolId()) ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <div class="flex items-center">
                                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                                                <span class="text-blue-600 font-semibold text-sm">
                                                                    <?= strtoupper(substr($student->getFullName(), 0, 1)) ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($student->getFullName()) ?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-4 px-6">
                                                        <?php 
                                                        $subjectInfo = $student->getSubjectInfo ?? null;
                                                        if ($subjectInfo): 
                                                        ?>
                                                            <div class="text-sm">
                                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($subjectInfo['subject_code']) ?></p>
                                                                <p class="text-gray-600"><?= htmlspecialchars($subjectInfo['subject_name']) ?></p>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-gray-400 text-sm">Multiple subjects</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <!-- Removed redundant modals - now redirecting to exam results page -->

    <!-- MVC Controller -->
    <script src="/js/controllers/faculty/FacultyStudentsController.js"></script>
</body>
</html>
