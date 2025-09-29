<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Examination System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@100;200;300;400;500;600;700;800;900&family=SF+Pro+Text:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .red-gradient { background: linear-gradient(135deg, #FF3B30 0%, #FF6B6B 100%); }

        .text-gradient {
            background: linear-gradient(135deg, var(--ios-blue) 0%, var(--ios-blue-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

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
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count(array_filter($users, fn($user) => $user->getRole() === 'student')) : 0 ?></p>
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
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count(array_filter($users, fn($user) => $user->getRole() === 'faculty')) : 0 ?></p>
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
                        <p class="sf-pro-display text-3xl font-bold text-gradient"><?= isset($users) ? count(array_filter($users, fn($user) => $user->getRole() === 'admin')) : 0 ?></p>
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
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/subjects" class="block w-full ios-button text-center text-white font-semibold py-4" style="background: linear-gradient(135deg, #34C759 0%, #30D158 100%);">
                        <i class="fas fa-book mr-2"></i>Manage Subjects
                    </a>
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments" class="block w-full ios-button text-center text-white font-semibold py-4" style="background: linear-gradient(135deg, #AF52DE 0%, #BF5AF2 100%);">
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
                        <h3 class="sf-pro-display text-xl font-bold text-gray-800">Add New User</h3>
                        <p class="text-gray-500">Create a new system user</p>
                    </div>
                </div>
                <button onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="userForm" method="POST" action="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add">
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
                    <button type="submit" class="flex-1 ios-button text-white font-semibold py-4">
                        <i class="fas fa-plus mr-2"></i>Create User
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
                        <?php 
                        // Sort users by role, then by year level and section for students
                        usort($users, function($a, $b) {
                            $roleOrder = ['admin' => 1, 'faculty' => 2, 'student' => 3];
                            $aRole = $roleOrder[$a->getRole()] ?? 4;
                            $bRole = $roleOrder[$b->getRole()] ?? 4;
                            
                            if ($aRole !== $bRole) {
                                return $aRole - $bRole;
                            }
                            
                            if ($a->getRole() === 'student' && $b->getRole() === 'student') {
                                $yearCompare = strcmp($a->getYearLevel(), $b->getYearLevel());
                                if ($yearCompare !== 0) return $yearCompare;
                                return strcmp($a->getSection(), $b->getSection());
                            }
                            
                            return strcmp($a->getFullName(), $b->getFullName());
                        });
                        ?>
                        <?php foreach ($users as $user): ?>
                            <div class="user-card bg-white border border-slate-200 rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1" data-role="<?= $user->getRole() ?>">
                                <!-- User Avatar and Role Badge -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br <?= $user->getRole() === 'admin' ? 'from-red-400 to-red-600' : ($user->getRole() === 'faculty' ? 'from-purple-400 to-purple-600' : 'from-blue-400 to-blue-600') ?> flex items-center justify-center">
                                            <i class="fas <?= $user->getRole() === 'admin' ? 'fa-user-shield' : ($user->getRole() === 'faculty' ? 'fa-chalkboard-teacher' : 'fa-user-graduate') ?> text-white text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-slate-900 text-lg"><?= htmlspecialchars($user->getFullName()) ?></h4>
                                            <p class="text-slate-500 text-sm"><?= htmlspecialchars($user->getSchoolId()) ?></p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $user->getRole() === 'admin' ? 'bg-red-100 text-red-700' : ($user->getRole() === 'faculty' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') ?>">
                                        <?= ucfirst($user->getRole()) ?>
                                    </span>
                                </div>

                                <!-- User Details -->
                                <div class="space-y-2 mb-4">
                                    <?php if ($user->getRole() === 'student'): ?>
                                        <div class="flex items-center text-sm text-slate-600">
                                            <i class="fas fa-graduation-cap w-4 mr-2"></i>
                                            <span><?= htmlspecialchars($user->getYearLevel()) ?> - Section <?= htmlspecialchars($user->getSection()) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex items-center text-sm text-slate-600">
                                        <i class="fas fa-calendar w-4 mr-2"></i>
                                        <span>Joined <?= date('M Y', strtotime($user->getCreatedAt())) ?></span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <button onclick="editUser(<?= htmlspecialchars(json_encode([
                                        'user_id' => $user->getUserId(),
                                        'school_id' => $user->getSchoolId(),
                                        'full_name' => $user->getFullName(),
                                        'role' => $user->getRole(),
                                        'year_level' => $user->getYearLevel(),
                                        'section' => $user->getSection()
                                    ])) ?>)" 
                                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </button>
                                    <button onclick="deleteUser(<?= $user->getUserId() ?>, '<?= htmlspecialchars($user->getFullName()) ?>', '<?= $user->getRole() ?>')" 
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
                <button onclick="closeDeleteUserModal()" 
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-4 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDeleteUser()" 
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

    <script>
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
                if (data.status === 'success') {
                    showToast('Logged out successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/login';
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Logout failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error: ' + error.message, 'error');
                // Re-enable the button
                logoutBtn.disabled = false;
                logoutBtn.innerHTML = originalText;
            });
        }

        function showAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
            // Reset form and hide student fields initially
            document.getElementById('userRole').value = '';
            toggleStudentFields();
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }

        function showUsersModal() {
            document.getElementById('usersModal').classList.remove('hidden');
        }

        function closeUsersModal() {
            document.getElementById('usersModal').classList.add('hidden');
        }

        function toggleStudentFields() {
            const roleSelect = document.getElementById('userRole');
            const yearLevelField = document.getElementById('yearLevelField');
            const sectionField = document.getElementById('sectionField');
            const yearLevelSelect = document.getElementById('userYearLevel');
            const sectionSelect = document.getElementById('userSection');
            
            if (roleSelect.value === 'student') {
                // Show fields for students
                yearLevelField.style.display = 'block';
                sectionField.style.display = 'block';
                yearLevelSelect.required = true;
                sectionSelect.required = true;
            } else {
                // Hide fields for admin and faculty
                yearLevelField.style.display = 'none';
                sectionField.style.display = 'none';
                yearLevelSelect.required = false;
                sectionSelect.required = false;
                yearLevelSelect.value = '';
                sectionSelect.value = '';
            }
        }

        function filterUsers(role) {
            const cards = document.querySelectorAll('.user-card');
            const buttons = document.querySelectorAll('[id^="filter"]');
            
            // Reset all button styles
            buttons.forEach(btn => {
                btn.className = 'px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors';
            });
            
            // Highlight active button
            const activeButton = document.getElementById('filter' + role.charAt(0).toUpperCase() + role.slice(1));
            activeButton.className = 'px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors';
            
            // Show/hide cards based on filter
            cards.forEach(card => {
                if (role === 'all' || card.dataset.role === role) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Add search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('userSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const cards = document.querySelectorAll('.user-card');
                    
                    cards.forEach(card => {
                        const name = card.querySelector('h4').textContent.toLowerCase();
                        const schoolId = card.querySelector('p').textContent.toLowerCase();
                        const role = card.dataset.role.toLowerCase();
                        
                        if (name.includes(searchTerm) || schoolId.includes(searchTerm) || role.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });

        let currentEditingUserId = null;

        function editUser(userData) {
            currentEditingUserId = userData.user_id;
            
            // Close the users modal first
            closeUsersModal();
            
            // Change modal title and button text
            document.querySelector('#addUserModal .sf-pro-display').textContent = 'Edit User';
            const submitButton = document.querySelector('#addUserModal button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
            
            // Pre-fill form with current data
            document.querySelector('input[name="school_id"]').value = userData.school_id;
            document.querySelector('input[name="full_name"]').value = userData.full_name;
            document.getElementById('userRole').value = userData.role;
            
            // Handle student fields
            if (userData.role === 'student') {
                document.getElementById('userYearLevel').value = userData.year_level || '';
                document.getElementById('userSection').value = userData.section || '';
            }
            
            // Trigger field visibility
            toggleStudentFields();
            
            // Show edit modal with a slight delay to ensure smooth transition
            setTimeout(() => {
                document.getElementById('addUserModal').classList.remove('hidden');
            }, 150);
        }

        let userToDelete = null;

        function deleteUser(userId, userName, userRole) {
            userToDelete = { id: userId, name: userName, role: userRole };
            
            // Update modal content
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteUserRole').textContent = userRole.charAt(0).toUpperCase() + userRole.slice(1);
            
            // Show delete modal
            document.getElementById('deleteUserModal').classList.remove('hidden');
        }

        function closeDeleteUserModal() {
            document.getElementById('deleteUserModal').classList.add('hidden');
            userToDelete = null;
        }

        function confirmDeleteUser() {
            if (!userToDelete) return;
            
            // Disable the delete button to prevent double-clicks
            const deleteBtn = document.querySelector('#deleteUserModal button[onclick="confirmDeleteUser()"]');
            const originalText = deleteBtn.innerHTML;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
            
            const formData = new FormData();
            formData.append('user_id', userToDelete.id);
            
            fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/delete/' + userToDelete.id, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success' || data.success) {
                    // Show success message briefly
                    showToast('User deleted successfully!', 'success');
                    closeDeleteUserModal();
                    // Reload page after a short delay to show the toast
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to delete user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error: ' + error.message, 'error');
                // Re-enable the button
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalText;
            });
        }

        // Add toast notification function
        function showToast(message, type = 'success') {
            // Remove existing toast if any
            const existingToast = document.getElementById('toast');
            if (existingToast) {
                existingToast.remove();
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            }`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }, 3000);
        }

        // Scores Modal Functions
        function showScoresModal() {
            const modal = document.getElementById('scoresModal');
            modal.classList.remove('hidden');
            loadScoresBySubject();
        }

        function closeScoresModal() {
            document.getElementById('scoresModal').classList.add('hidden');
        }

        function showScoreAnalytics() {
            // Placeholder for future analytics functionality
            showToast('Score Analytics feature coming soon!', 'info');
        }

        async function loadScoresBySubject() {
            const loadingDiv = document.getElementById('scoresLoading');
            const dataDiv = document.getElementById('scoresData');
            const noDataDiv = document.getElementById('noScoresData');

            // Show loading state
            loadingDiv.classList.remove('hidden');
            dataDiv.classList.add('hidden');
            noDataDiv.classList.add('hidden');

            try {
                // Fetch scores data from backend
                const response = await fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/api/admin/scores-by-subject');
                const result = await response.json();

                if (result.success && result.data && result.data.length > 0) {
                    displayScoresBySubject(result.data);
                    populateSubjectFilter(result.subjects || []);
                } else {
                    // Show no data state
                    loadingDiv.classList.add('hidden');
                    noDataDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading scores:', error);
                loadingDiv.classList.add('hidden');
                noDataDiv.classList.remove('hidden');
                showToast('Error loading scores data', 'error');
            }
        }

        function displayScoresBySubject(scoresData) {
            const loadingDiv = document.getElementById('scoresLoading');
            const dataDiv = document.getElementById('scoresData');
            
            loadingDiv.classList.add('hidden');
            dataDiv.classList.remove('hidden');
            
            // Group scores by subject
            const subjectGroups = {};
            scoresData.forEach(score => {
                const subjectKey = `${score.subject_code} - ${score.subject_name}`;
                if (!subjectGroups[subjectKey]) {
                    subjectGroups[subjectKey] = {
                        subject: score,
                        exams: []
                    };
                }
                subjectGroups[subjectKey].exams.push(score);
            });

            // Generate HTML for each subject group
            let html = '';
            Object.keys(subjectGroups).forEach(subjectKey => {
                const group = subjectGroups[subjectKey];
                const avgScore = group.exams.reduce((sum, exam) => sum + parseFloat(exam.score || 0), 0) / group.exams.length;
                
                html += `
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                                <div>
                                    <h5 class="text-lg font-bold text-gray-800">${group.subject.subject_code}</h5>
                                    <p class="text-gray-600">${group.subject.subject_name}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Average Score</p>
                                <p class="text-2xl font-bold text-green-600">${avgScore.toFixed(1)}%</p>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 font-semibold text-gray-700">Student</th>
                                        <th class="text-left py-2 font-semibold text-gray-700">Exam</th>
                                        <th class="text-left py-2 font-semibold text-gray-700">Score</th>
                                        <th class="text-left py-2 font-semibold text-gray-700">Date</th>
                                        <th class="text-left py-2 font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                group.exams.forEach(exam => {
                    const scoreColor = parseFloat(exam.score || 0) >= 75 ? 'text-green-600' : 
                                     parseFloat(exam.score || 0) >= 60 ? 'text-yellow-600' : 'text-red-600';
                    const statusColor = exam.status === 'completed' ? 'bg-green-100 text-green-800' : 
                                       exam.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800';
                    
                    html += `
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-gray-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">${exam.student_name || 'N/A'}</p>
                                        <p class="text-xs text-gray-500">${exam.school_id || ''}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <p class="font-medium text-gray-800">${exam.exam_title || 'N/A'}</p>
                                <p class="text-xs text-gray-500">${exam.exam_type || ''}</p>
                            </td>
                            <td class="py-3">
                                <span class="text-lg font-bold ${scoreColor}">${exam.score || 0}%</span>
                            </td>
                            <td class="py-3 text-gray-600">
                                ${exam.end_time ? new Date(exam.end_time).toLocaleDateString() : 'N/A'}
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium ${statusColor}">
                                    ${exam.status || 'Unknown'}
                                </span>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });
            
            dataDiv.innerHTML = html;
        }

        function populateSubjectFilter(subjects) {
            const subjectFilter = document.getElementById('subjectFilter');
            
            // Clear existing options except "All Subjects"
            while (subjectFilter.children.length > 1) {
                subjectFilter.removeChild(subjectFilter.lastChild);
            }
            
            // Add subject options
            subjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.subject_id;
                option.textContent = `${subject.subject_code} - ${subject.subject_name}`;
                subjectFilter.appendChild(option);
            });
        }

        // Add event listeners for filters
        document.addEventListener('DOMContentLoaded', function() {
            const subjectFilter = document.getElementById('subjectFilter');
            const yearFilter = document.getElementById('yearFilter');
            
            if (subjectFilter) {
                subjectFilter.addEventListener('change', filterScores);
            }
            if (yearFilter) {
                yearFilter.addEventListener('change', filterScores);
            }
        });

        function filterScores() {
            // This would filter the displayed scores based on selected filters
            // Implementation depends on how you want to handle the filtering
            loadScoresBySubject();
        }

        // Override the showAddUserModal to reset for new users
        function showAddUserModal() {
            currentEditingUserId = null;
            
            // Reset modal title and button text
            document.querySelector('#addUserModal .sf-pro-display').textContent = 'Add New User';
            const submitButton = document.querySelector('#addUserModal button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-plus mr-2"></i>Create User';
            
            // Reset form
            document.getElementById('userForm').reset();
            
            document.getElementById('addUserModal').classList.remove('hidden');
            // Reset form and hide student fields initially
            document.getElementById('userRole').value = '';
            toggleStudentFields();
        }

        // Handle form submission for both add and edit
        document.addEventListener('DOMContentLoaded', function() {
            const userForm = document.getElementById('userForm');
            if (userForm) {
                userForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    let url, method = 'POST';
                    
                    if (currentEditingUserId) {
                        // Editing existing user
                        url = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/edit/' + currentEditingUserId;
                        formData.append('user_id', currentEditingUserId);
                    } else {
                        // Adding new user
                        url = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/users/add';
                    }
                    
                    fetch(url, {
                        method: method,
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success' || data.success) {
                            const action = currentEditingUserId ? 'updated' : 'created';
                            showToast(`User ${action} successfully!`, 'success');
                            closeAddUserModal();
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showToast('Error: ' + (data.message || 'Operation failed'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while processing your request.', 'error');
                    });
                });
            }
        });
    </script>
</body>
</html>