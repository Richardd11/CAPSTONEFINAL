<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/dashboard-shared.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-12 mb-12 relative">
        <div class="container mx-auto px-8 relative z-10">
            <div class="flex justify-between items-center">
                <div class="animate-fade-in-up">
                    <div class="flex items-center mb-4">
                        <div class="icon-container blue-gradient mr-4">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h1 class="sf-pro-display text-4xl font-bold mb-2 tracking-tight">
                                Admin Dashboard
                            </h1>
                            <p class="text-xl opacity-90 font-medium">
                                System Administration & Management
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

    <div class="container mx-auto px-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            <div class="stats-card p-8 animate-fade-in-up">
                <div class="flex items-center">
                    <div class="icon-container blue-gradient mr-6">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Users</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count($users) : 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-8 animate-fade-in-up animate-delay-100">
                <div class="flex items-center">
                    <div class="icon-container green-gradient mr-6">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Students</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count(array_filter($users, fn($user) => (is_object($user) ? $user->getRole() : $user['role']) === 'student')) : 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-8 animate-fade-in-up animate-delay-200">
                <div class="flex items-center">
                    <div class="icon-container purple-gradient mr-6">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Faculty</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count(array_filter($users, fn($user) => (is_object($user) ? $user->getRole() : $user['role']) === 'faculty')) : 0 ?></p>
                    </div>
                </div>
            </div>
            
            <div class="stats-card p-8 animate-fade-in-up animate-delay-300">
                <div class="flex items-center">
                    <div class="icon-container red-gradient mr-6">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Admins</p>
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count(array_filter($users, fn($user) => (is_object($user) ? $user->getRole() : $user['role']) === 'admin')) : 0 ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- User Management -->
            <div class="action-card p-8 animate-fade-in-up animate-delay-400">
                <div class="flex items-center mb-8">
                    <div class="icon-container blue-gradient mr-4">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <div>
                        <h2 class="sf-pro-display text-2xl font-bold text-gray-800">
                            User Management
                        </h2>
                        <p class="text-gray-500 font-medium">Manage system users and permissions</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <button onclick="showAddUserModal()" class="w-full ios-button text-white font-semibold py-4">
                        <i class="fas fa-plus mr-2"></i>Add New User
                    </button>
                    <button onclick="showUsersModal()" class="w-full bg-gray-100 text-gray-700 py-4 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        <i class="fas fa-list mr-2"></i>View All Users
                    </button>
                </div>
            </div>

            <!-- System Management -->
            <div class="action-card p-8 animate-fade-in-up animate-delay-400">
                <div class="flex items-center mb-8">
                    <div class="icon-container purple-gradient mr-4">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div>
                        <h2 class="sf-pro-display text-2xl font-bold text-gray-800">
                            System Management
                        </h2>
                        <p class="text-gray-500 font-medium">Configure subjects and assignments</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <a href="/admin/subjects" class="block w-full ios-button text-center text-white font-semibold py-4" style="background: linear-gradient(135deg, #34C759 0%, #30D158 100%);">
                        <i class="fas fa-book mr-2"></i>Manage Subjects
                    </a>
                    <a href="/admin/assignments" class="block w-full ios-button text-center text-white font-semibold py-4" style="background: linear-gradient(135deg, #AF52DE 0%, #BF5AF2 100%);">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Faculty Assignments
                    </a>
                </div>
            </div>

            <!-- Score Management -->
            <div class="action-card p-8 animate-fade-in-up animate-delay-500">
                <div class="flex items-center mb-8">
                    <div class="icon-container green-gradient mr-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h2 class="sf-pro-display text-2xl font-bold text-gray-800">
                            Score Management
                        </h2>
                        <p class="text-gray-500 font-medium">View and analyze exam scores by subject</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <button onclick="showScoresModal()" class="w-full ios-button text-white font-semibold py-4" style="background: linear-gradient(135deg, #FF9500 0%, #FFCC02 100%);">
                        <i class="fas fa-chart-bar mr-2"></i>View Scores by Subject
                    </button>
                    <button onclick="showScoreAnalytics()" class="w-full bg-gray-100 text-gray-700 py-4 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        <i class="fas fa-analytics mr-2"></i>Score Analytics
                    </button>
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

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="modal-content p-8 max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-8">
                <div class="flex items-center">
                    <div class="icon-container blue-gradient mr-4">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h3 id="userModalTitle" class="sf-pro-display text-xl font-bold text-gray-800">Add New User</h3>
                        <p class="text-gray-500">Create a new system user</p>
                    </div>
                </div>
                <button onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="userForm" method="POST" action="/admin/users/add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">School ID</label>
                        <input type="text" name="school_id" required class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Full Name</label>
                        <input type="text" name="full_name" required class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Role</label>
                        <select id="userRole" name="role" required onchange="toggleStudentFields()" class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div id="yearLevelField" style="display: none;">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Year Level</label>
                        <select id="userYearLevel" name="year_level" class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div id="sectionField" style="display: none;">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Section</label>
                        <select id="userSection" name="section" class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="closeAddUserModal()" class="flex-1 bg-gray-100 text-gray-700 py-4 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="userSubmitBtn" class="flex-1 ios-button text-white font-semibold py-4">
                        <i class="fas fa-plus mr-2"></i><span id="userSubmitBtnText">Create User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users List Modal -->
    <div id="usersModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl mx-4 max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-white">Users</h3>
                        <p class="text-slate-300 text-sm">Manage your team</p>
                    </div>
                    <button onclick="closeUsersModal()" class="text-slate-400 hover:text-white p-2 rounded-lg hover:bg-slate-700 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="px-8 py-6 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                    <div class="relative flex-1 max-w-md">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="userSearch" placeholder="Search users..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex gap-2">
                        <button onclick="filterUsers('all')" id="filterAll" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">All</button>
                        <button onclick="filterUsers('admin')" id="filterAdmin" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">Admin</button>
                        <button onclick="filterUsers('faculty')" id="filterFaculty" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">Faculty</button>
                        <button onclick="filterUsers('student')" id="filterStudent" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">Students</button>
                    </div>
                </div>
            </div>

            <!-- Users Grid -->
            <div class="px-8 py-6 max-h-[60vh] overflow-y-auto">
                <div id="usersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (isset($users) && is_array($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <div class="user-card bg-white border border-slate-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1" data-role="<?= htmlspecialchars($user['role']) ?>">
                                <!-- User Avatar and Role Badge -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br <?= htmlspecialchars($user['avatar_gradient']) ?> flex items-center justify-center">
                                            <i class="fas <?= htmlspecialchars($user['role_icon']) ?> text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-900 text-lg"><?= htmlspecialchars($user['full_name']) ?></h4>
                                            <p class="text-slate-500 text-sm"><?= htmlspecialchars($user['school_id']) ?></p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= htmlspecialchars($user['role_badge_class']) ?>">
                                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                    </span>
                                </div>

                                <!-- User Details -->
                                <div class="space-y-2 mb-4">
                                    <?php if ($user['role'] === 'student' && !empty($user['year_level'])): ?>
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i class="fas fa-graduation-cap w-4 mr-2"></i>
                                            <span><?= htmlspecialchars($user['year_level']) ?> - Section <?= htmlspecialchars($user['section']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex items-center text-sm text-slate-600">
                                        <i class="fas fa-calendar w-4 mr-2"></i>
                                        <span>Joined <?= date('M Y', strtotime($user['created_at'])) ?></span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <button onclick="editUser(<?= htmlspecialchars(json_encode([
                                        'user_id' => $user['user_id'],
                                        'school_id' => $user['school_id'],
                                        'full_name' => $user['full_name'],
                                        'role' => $user['role'],
                                        'year_level' => $user['year_level'],
                                        'section' => $user['section']
                                    ])) ?>)" 
                                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button onclick="deleteUser(<?= htmlspecialchars($user['user_id']) ?>, '<?= htmlspecialchars($user['full_name']) ?>', '<?= htmlspecialchars($user['role']) ?>')" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-full flex flex-col items-center justify-center py-12">
                            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                                <i class="fas fa-users text-slate-400 text-2xl"></i>
                            </div>
                            <p class="text-slate-500 text-lg font-medium">No users found</p>
                            <p class="text-slate-400 text-sm">Try adjusting your search or filters</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Delete User</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone</p>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mr-3">
                        <i class="fas fa-user text-slate-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900" id="deleteUserName">User Name</p>
                        <p class="text-slate-500 text-sm" id="deleteUserRole">Role</p>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button id="cancelDeleteBtn" 
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-4 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" 
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Scores Modal -->
    <div id="scoresModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-4 max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-orange-500 to-yellow-500 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center mr-4">
                            <i class="fas fa-chart-bar text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="sf-pro-display text-2xl font-bold text-white">Exam Scores by Subject</h3>
                            <p class="text-white/80">View and analyze student performance across all subjects</p>
                        </div>
                    </div>
                    <button onclick="closeScoresModal()" class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-white text-lg"></i>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-8 overflow-y-auto max-h-[calc(90vh-120px)]">
                <!-- Subject Filter -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold text-gray-800">Filter by Subject</h4>
                        <div class="flex items-center space-x-4">
                            <select id="subjectFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">All Subjects</option>
                                <!-- Subjects will be populated dynamically -->
                            </select>
                            <select id="yearFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">All Year Levels</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Scores Content -->
                <div id="scoresContent">
                    <!-- Loading State -->
                    <div id="scoresLoading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mb-4"></div>
                        <p class="text-gray-600">Loading exam scores...</p>
                    </div>
                    
                    <!-- Scores will be populated here -->
                    <div id="scoresData" class="hidden space-y-6">
                        <!-- Subject groups will be added dynamically -->
                    </div>
                    
                    <!-- No Data State -->
                    <div id="noScoresData" class="hidden text-center py-12">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-bar text-gray-400 text-3xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-700 mb-2">No Exam Scores Found</h4>
                        <p class="text-gray-500">No exam scores are available for the selected criteria.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Load shared dashboard JavaScript -->
    <script src="/assets/js/dashboard-shared.js"></script>
    
    <!-- Load MVC Architecture for Admin Dashboard -->
    <!-- Services -->
    <script src="/js/services/toast-service.js"></script>
    <script src="/js/services/APIService.js"></script>
    <script src="/js/services/UserManagementService.js"></script>
    <script src="/js/services/ScoreService.js"></script>
    
    <!-- Models -->
    <script src="/js/models/User.js"></script>
    
    <!-- Views -->
    <script src="/js/views/UserManagementView.js"></script>
    <script src="/js/views/ScoreView.js"></script>
    
    <!-- Controllers -->
    <script src="/js/controllers/UserManagementController.js"></script>
    <script src="/js/controllers/AdminDashboardController.js"></script>
    <script src="/js/controllers/ScoreController.js"></script>
    
    <!-- Initialize MVC and expose global functions -->
    <script src="/js/admin-dashboard-mvc.js"></script>
</body>
</html>