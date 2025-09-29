<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Assignments - Admin Dashboard</title>
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
            color: #1C1C1E;
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

        .animate-delay-200 { 
            animation-delay: 0.2s; 
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-12 mb-12 relative">
        <div class="container mx-auto px-8 relative z-10">
            <div class="flex justify-between items-center">
                <div class="animate-fade-in-up">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mr-4">
                            <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="sf-pro-display text-4xl font-bold mb-2 tracking-tight">
                                Faculty Assignments
                            </h1>
                            <p class="text-xl opacity-90 font-medium">
                                Manage faculty-subject assignments efficiently
                            </p>
                        </div>
                    </div>
                </div>
                <div class="animate-fade-in-up animate-delay-200">
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/dashboard" 
                       class="bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white px-8 py-3 rounded-2xl hover:bg-white hover:text-blue-600 transition-all duration-300 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-8">
        <!-- Action Bar -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="sf-pro-display text-2xl font-bold text-gray-800">Faculty Assignments</h2>
                <p class="text-gray-600">Assign faculty members to subjects and sections</p>
            </div>
            <button onclick="showAddAssignmentModal()" class="ios-button">
                <i class="fas fa-plus mr-2"></i>Create Assignment
            </button>
        </div>

        <!-- Assignments by Year Level -->
        <div id="assignmentsContainer">
            <?php if (isset($assignments) && !empty($assignments)): ?>
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
                    <div class="mb-12">
                        <!-- Year Level Header -->
                        <div class="ios-card p-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mr-4">
                                        <i class="fas fa-chalkboard-teacher text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="sf-pro-display text-2xl font-bold text-gray-800"><?= htmlspecialchars($yearLevel) ?></h3>
                                        <p class="text-gray-600">Faculty Subject Assignments</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="bg-purple-100 text-purple-800 px-4 py-2 rounded-full">
                                        <span class="font-semibold"><?= count($yearAssignments) ?></span>
                                        <span class="text-sm">Assignment<?= count($yearAssignments) !== 1 ? 's' : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Assignments Grid for this Year Level -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($yearAssignments as $assignment): ?>
                                <div class="ios-card p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="sf-pro-display text-lg font-bold text-gray-900 mb-1">
                                                <?= htmlspecialchars($assignment->getSubjectCode() ?? 'N/A') ?>
                                            </h3>
                                            <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($assignment->getSubjectName() ?? '') ?></p>
                                            <p class="text-sm text-gray-500">Faculty: <?= htmlspecialchars($assignment->getFacultyName() ?? 'Unassigned') ?></p>
                                        </div>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                            <?= ucfirst($assignment->getStatus() ?? 'active') ?>
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-users mr-2 text-blue-500"></i>
                                            <span>Section <?= htmlspecialchars($assignment->getSection() ?? '') ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-calendar mr-2 text-green-500"></i>
                                            <span><?= htmlspecialchars($assignment->getSemester() ?? '') ?></span>
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-school mr-2 text-purple-500"></i>
                                            <span>AY <?= htmlspecialchars($assignment->getAcademicYear() ?? '') ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <button onclick="editAssignment(<?= htmlspecialchars(json_encode([
                                            'id' => $assignment->getId(),
                                            'subject_id' => $assignment->getSubjectId(),
                                            'faculty_id' => $assignment->getFacultyId(),
                                            'year_level' => $assignment->getYearLevel(),
                                            'section' => $assignment->getSection(),
                                            'academic_year' => $assignment->getAcademicYear(),
                                            'semester' => $assignment->getSemester(),
                                            'status' => $assignment->getStatus(),
                                            'notes' => $assignment->getNotes()
                                        ])) ?>)" 
                                                class="flex-1 bg-blue-100 text-blue-700 py-2 px-3 rounded-lg text-sm font-semibold hover:bg-blue-200 transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </button>
                                        <button onclick="deleteAssignment(<?= $assignment->getId() ?? 0 ?>)" 
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
                    <i class="fas fa-chalkboard-teacher text-6xl text-gray-400 mb-4"></i>
                    <h3 class="sf-pro-display text-xl font-semibold text-gray-700 mb-2">No Assignments Found</h3>
                    <p class="text-gray-500 mb-6">Start by creating faculty-subject assignments.</p>
                    <button onclick="showAddAssignmentModal()" class="ios-button">
                        <i class="fas fa-plus mr-2"></i>Create First Assignment
                    </button>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Add/Edit Assignment Modal -->
    <div id="assignmentModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="modal-content p-8 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="sf-pro-display text-xl font-bold text-gray-800" id="assignmentModalTitle">Create Assignment</h3>
                <button onclick="closeAssignmentModal()" class="text-gray-400 hover:text-gray-600 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="assignmentForm" method="POST">
                <input type="hidden" id="assignmentId" name="assignment_id">
                <input type="hidden" id="assignmentAction" name="action" value="add">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Subject</label>
                        <select id="subjectId" name="subject_id" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Select Subject</option>
                            <?php if (isset($subjects) && !empty($subjects)): ?>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['subject_id'] ?>">
                                        <?= htmlspecialchars($subject['subject_code']) ?> - <?= htmlspecialchars($subject['subject_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Faculty</label>
                        <select id="facultyId" name="faculty_id" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Select Faculty</option>
                            <?php if (isset($faculty) && !empty($faculty)): ?>
                                <?php foreach ($faculty as $member): ?>
                                    <option value="<?= $member['user_id'] ?>">
                                        <?= htmlspecialchars($member['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Year Level</label>
                        <select id="assignmentYearLevel" name="year_level" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Section</label>
                        <select id="assignmentSection" name="section" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Status</label>
                        <select id="assignmentStatus" name="status" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Academic Year</label>
                        <input type="text" id="assignmentAcademicYear" name="academic_year" value="2024-2025" required 
                               class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Semester</label>
                        <select id="assignmentSemester" name="semester" required 
                                class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Notes</label>
                    <textarea id="assignmentNotes" name="notes" rows="3" 
                              class="w-full p-4 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                              placeholder="Optional notes about this assignment..."></textarea>
                </div>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="closeAssignmentModal()" 
                            class="flex-1 bg-gray-100 text-gray-700 py-4 px-4 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 ios-button py-4">
                        <i class="fas fa-save mr-2"></i><span id="assignmentSubmitText">Create Assignment</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Assignment Confirmation Modal -->
    <div id="deleteAssignmentModal" class="fixed inset-0 modal-backdrop hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900 mb-2">Delete Assignment</h3>
                <p class="text-slate-500 text-sm">This action cannot be undone</p>
            </div>
            
            <div class="bg-slate-50 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center mr-3">
                        <i class="fas fa-chalkboard-teacher text-slate-600"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900" id="deleteAssignmentInfo">Assignment</p>
                        <p class="text-slate-500 text-sm">Faculty Assignment</p>
                    </div>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button onclick="closeDeleteAssignmentModal()" 
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-4 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <button onclick="confirmDeleteAssignment()" 
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-lg font-medium transition-colors">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        function showAddAssignmentModal() {
            document.getElementById('assignmentModalTitle').textContent = 'Create Assignment';
            document.getElementById('assignmentSubmitText').textContent = 'Create Assignment';
            document.getElementById('assignmentAction').value = 'add';
            document.getElementById('assignmentForm').reset();
            document.getElementById('assignmentId').value = '';
            document.getElementById('assignmentAcademicYear').value = '2024-2025';
            document.getElementById('assignmentModal').classList.remove('hidden');
        }

        function editAssignment(assignment) {
            document.getElementById('assignmentModalTitle').textContent = 'Edit Assignment';
            document.getElementById('assignmentSubmitText').textContent = 'Update Assignment';
            document.getElementById('assignmentAction').value = 'edit';
            
            document.getElementById('assignmentId').value = assignment.id;
            document.getElementById('subjectId').value = assignment.subject_id;
            document.getElementById('facultyId').value = assignment.faculty_id;
            document.getElementById('assignmentYearLevel').value = assignment.year_level;
            document.getElementById('assignmentSection').value = assignment.section;
            document.getElementById('assignmentStatus').value = assignment.status;
            document.getElementById('assignmentAcademicYear').value = assignment.academic_year;
            document.getElementById('assignmentSemester').value = assignment.semester;
            document.getElementById('assignmentNotes').value = assignment.notes || '';
            
            document.getElementById('assignmentModal').classList.remove('hidden');
        }

        function closeAssignmentModal() {
            document.getElementById('assignmentModal').classList.add('hidden');
        }

        let assignmentToDelete = null;

        function deleteAssignment(assignmentId) {
            assignmentToDelete = { id: assignmentId };
            
            // Update modal content
            document.getElementById('deleteAssignmentInfo').textContent = `Assignment #${assignmentId}`;
            
            // Show delete modal
            document.getElementById('deleteAssignmentModal').classList.remove('hidden');
        }

        function closeDeleteAssignmentModal() {
            document.getElementById('deleteAssignmentModal').classList.add('hidden');
            assignmentToDelete = null;
        }

        function confirmDeleteAssignment() {
            if (!assignmentToDelete) return;
            
            // Disable the delete button to prevent double-clicks
            const deleteBtn = document.querySelector('#deleteAssignmentModal button[onclick="confirmDeleteAssignment()"]');
            const originalText = deleteBtn.innerHTML;
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
            
            const formData = new FormData();
            formData.append('assignment_id', assignmentToDelete.id);
            
            fetch('<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/delete', {
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
                    showToast('Assignment deleted successfully!', 'success');
                    closeDeleteAssignmentModal();
                    // Reload page after a short delay to show the toast
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to delete assignment');
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

        // Handle form submission
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const action = formData.get('action');
            
            let url = '<?= dirname($_SERVER['SCRIPT_NAME']) ?>/admin/assignments/';
            if (action === 'add') {
                url += 'add';
            } else if (action === 'edit') {
                url += 'edit';
            }
            
            fetch(url, {
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
                    const actionText = action === 'add' ? 'created' : 'updated';
                    showToast(`Assignment ${actionText} successfully!`, 'success');
                    closeAssignmentModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast('Error: ' + (data.message || 'Unknown error occurred'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while processing your request.', 'error');
            });
        });
    </script>
</body>
</html>
