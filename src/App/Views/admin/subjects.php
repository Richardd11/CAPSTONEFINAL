<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ios-blue: #007AFF;
            --ios-blue-light: #5AC8FA;
            --ios-blue-dark: #0051D5;
        }

        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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

        .ios-button {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.3);
        }

        .ios-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0, 122, 255, 0.4);
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 50%, var(--ios-blue-dark) 100%);
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

        .subject-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .subject-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 122, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8">
        <div class="container mx-auto px-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="sf-pro-display text-3xl font-bold mb-2">Subject Management</h1>
                    <p class="text-xl opacity-90">Manage academic subjects and courses</p>
                </div>
                <a href="/admin/dashboard" 
                   class="bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white px-6 py-3 rounded-xl hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-8">
        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="sf-pro-display text-2xl font-bold text-gray-800">All Subjects</h2>
                <p class="text-gray-600">Manage your academic subjects and courses</p>
            </div>
            <button onclick="showAddSubjectModal()" class="ios-button">
                <i class="fas fa-plus mr-2"></i>Add New Subject
            </button>
        </div>

        <!-- Search and Filter Bar -->
        <div class="ios-card p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="subjectSearch" placeholder="Search subjects by code or name..." 
                               class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>
                <div class="flex gap-3">
                    <select id="yearFilter" class="px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">All Year Levels</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                    <select id="semesterFilter" class="px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option value="">All Semesters</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Subjects by Year Level -->
        <div id="subjectsContainer">
            <?php if (isset($subjects) && !empty($subjects)): ?>
                <?php 
                // Group subjects by year level
                $subjectsByYear = [];
                foreach ($subjects as $subject) {
                    $yearLevel = $subject['year_level'];
                    if (!isset($subjectsByYear[$yearLevel])) {
                        $subjectsByYear[$yearLevel] = [];
                    }
                    $subjectsByYear[$yearLevel][] = $subject;
                }
                
                // Sort year levels
                $yearOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
                uksort($subjectsByYear, function($a, $b) use ($yearOrder) {
                    $aPos = array_search($a, $yearOrder);
                    $bPos = array_search($b, $yearOrder);
                    return $aPos - $bPos;
                });
                ?>
                
                <?php foreach ($subjectsByYear as $yearLevel => $yearSubjects): ?>
                    <!-- Year Level Section -->
                    <div class="mb-12" data-year-section="<?= htmlspecialchars($yearLevel) ?>">
                        <!-- Year Level Header -->
                        <div class="ios-card p-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4">
                                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="sf-pro-display text-2xl font-bold text-gray-800"><?= htmlspecialchars($yearLevel) ?></h3>
                                        <p class="text-gray-600">Academic Subjects & Courses</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full">
                                        <span class="font-semibold"><?= count($yearSubjects) ?></span>
                                        <span class="text-sm">Subject<?= count($yearSubjects) !== 1 ? 's' : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Subjects Grid for this Year Level -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($yearSubjects as $subject): ?>
                                <div class="subject-card p-6" data-semester="<?= htmlspecialchars($subject['semester']) ?>">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="sf-pro-display text-xl font-bold text-gray-900 mb-1">
                                                <?= htmlspecialchars($subject['subject_code']) ?>
                                            </h3>
                                            <p class="text-gray-600 mb-2"><?= htmlspecialchars($subject['subject_name']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($subject['description'] ?? '') ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-2 text-green-500"></i>
                                            <span><?= htmlspecialchars($subject['semester']) ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-book mr-2 text-purple-500"></i>
                                            <span><?= htmlspecialchars($subject['units']) ?> Units</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <button onclick="editSubject(<?= htmlspecialchars(json_encode($subject)) ?>)" 
                                                class="flex-1 bg-blue-100 text-blue-700 py-2 px-3 rounded-lg text-sm font-semibold hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <button onclick="deleteSubject(<?= $subject['subject_id'] ?>, '<?= htmlspecialchars($subject['subject_code']) ?>')" 
                                                class="flex-1 bg-red-100 text-red-700 py-2 px-3 rounded-lg text-sm font-semibold hover:bg-red-200 transition-colors">
                                            <i class="fas fa-trash mr-1"></i>Delete
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
                    <h3 class="sf-pro-display text-xl font-semibold text-gray-700 mb-2">No Subjects Found</h3>
                    <p class="text-gray-500 mb-6">Start by adding your first subject to the system.</p>
                    <button onclick="showAddSubjectModal()" class="ios-button">
                        <i class="fas fa-plus mr-2"></i>Add First Subject
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Subject Modal -->
    <div id="subjectModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="modal-content p-8 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="sf-pro-display text-xl font-bold text-gray-800" id="modalTitle">Add New Subject</h3>
                <button onclick="closeSubjectModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="subjectForm" method="POST">
                <input type="hidden" id="subjectId" name="subject_id">
                <input type="hidden" id="formAction" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Subject Code</label>
                        <input type="text" id="subjectCode" name="subject_code" required 
                               class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Units</label>
                        <select id="units" name="units" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="1">1 Unit</option>
                            <option value="2">2 Units</option>
                            <option value="3" selected>3 Units</option>
                            <option value="4">4 Units</option>
                            <option value="5">5 Units</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Subject Name</label>
                    <input type="text" id="subjectName" name="subject_name" required 
                           class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Year Level</label>
                        <select id="yearLevel" name="year_level" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Semester</label>
                        <select id="semester" name="semester" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="closeSubjectModal()" 
                            class="flex-1 bg-gray-100 text-gray-700 py-4 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 ios-button py-4">
                        <i class="fas fa-save mr-2"></i><span id="submitText">Add Subject</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Subject Confirmation Modal -->
    <div id="deleteSubjectModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Delete Subject</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone</p>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mr-3">
                        <i class="fas fa-book text-slate-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900" id="deleteSubjectCode">Subject Code</p>
                        <p class="text-slate-500 text-sm">Subject</p>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button onclick="closeDeleteSubjectModal()" 
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-4 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDeleteSubject()" 
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Load MVC Architecture for Subjects -->
    <!-- Services -->
    <script src="/js/services/APIService.js"></script>
    <script src="/js/services/SubjectManagementService.js"></script>
    
    <!-- Models -->
    <script src="/js/models/Subject.js"></script>
    
    <!-- Controllers -->
    <script src="/js/controllers/SubjectListController.js"></script>
    
    <!-- Initialize MVC and expose global functions -->
    <script src="/js/subjects-mvc.js"></script>
</body>
</html>
