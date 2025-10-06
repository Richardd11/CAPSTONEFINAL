<!-- Top Section - Add Assignment Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-link mr-2 text-primary-600"></i>
                Subject Assignments
            </h4>
            <p class="text-grey-600">Manage faculty assignments to subjects by year level and section</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1" onclick="showAddAssignmentModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Assignment
            </button>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-link text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Total Assignments</p>
                <p class="text-2xl font-semibold text-grey-900" id="totalAssignments">0</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Active</p>
                <p class="text-2xl font-semibold text-grey-900" id="activeAssignments">0</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Pending</p>
                <p class="text-2xl font-semibold text-grey-900" id="pendingAssignments">0</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-lg shadow-md border border-grey-200">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-grey-600">Unassigned</p>
                <p class="text-2xl font-semibold text-grey-900" id="unassignedSubjects">0</p>
            </div>
        </div>
    </div>
</div>



<!-- Assignments Table -->
<div class="bg-white rounded-lg shadow-md border border-grey-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-grey-200">
        <h5 class="text-lg font-semibold text-grey-800">
            <i class="fas fa-list mr-2"></i>
            Assignment List
        </h5>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-grey-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Faculty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Year & Section</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Academic Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Semester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-grey-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="assignmentsTableBody" class="bg-white divide-y divide-grey-200">
                <!-- Assignments will be loaded here dynamically -->
            </tbody>
        </table>
    </div>
    
    <!-- Empty State -->
    <div id="assignmentsEmptyState" class="hidden text-center py-12">
        <i class="fas fa-link text-6xl text-grey-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-grey-700 mb-2">No Assignments Found</h4>
        <p class="text-grey-500 mb-6">Start by adding your first subject assignment.</p>
        <button onclick="showAddAssignmentModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300">
            <i class="fas fa-plus mr-2"></i>
            Add First Assignment
        </button>
    </div>
</div>

<!-- Add Assignment Modal -->
<div id="addAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div>
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Assignment
                    </h3>
                </div>
            </div>
            
            <form id="addAssignmentForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Subject Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject *</label>
                        <select name="subject_id" id="assignmentSubject" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['subject_id'] ?>">
                                    <?= htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Faculty Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Faculty Member *</label>
                        <select name="faculty_id" id="assignmentFaculty" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Faculty Member</option>
                            <?php foreach ($faculty as $facultyMember): ?>
                                <option value="<?= $facultyMember['user_id'] ?>">
                                    <?= htmlspecialchars($facultyMember['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Year Level -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" id="assignmentYearLevel" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($assignmentYearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Section -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select name="section" id="assignmentSection" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Section</option>
                            <?php foreach ($assignmentSections as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Academic Year -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Academic Year *</label>
                        <select name="academic_year" id="assignmentAcademicYear" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Academic Year</option>
                            <?php foreach ($academicYears as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" id="assignmentSemester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($assignmentSemesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Status</label>
                        <select name="status" id="assignmentStatus" 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <?php foreach ($assignmentStatuses as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>" <?= $key === 'active' ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Notes</label>
                        <textarea name="notes" id="assignmentNotes" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Additional notes about this assignment..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideAddAssignmentModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Add Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div id="editAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div>
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Assignment
                    </h3>
                </div>
            </div>
            
            <form id="editAssignmentForm" class="p-6">
                <input type="hidden" name="assignment_id" id="editAssignmentId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Subject Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject *</label>
                        <select name="subject_id" id="editAssignmentSubject" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?= $subject['subject_id'] ?>">
                                    <?= htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Faculty Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Faculty Member *</label>
                        <select name="faculty_id" id="editAssignmentFaculty" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Faculty Member</option>
                            <?php foreach ($faculty as $facultyMember): ?>
                                <option value="<?= $facultyMember['user_id'] ?>">
                                    <?= htmlspecialchars($facultyMember['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Year Level -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" id="editAssignmentYearLevel" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($assignmentYearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Section -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select name="section" id="editAssignmentSection" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Section</option>
                            <?php foreach ($assignmentSections as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Academic Year -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Academic Year *</label>
                        <select name="academic_year" id="editAssignmentAcademicYear" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Academic Year</option>
                            <?php foreach ($academicYears as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" id="editAssignmentSemester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($assignmentSemesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Status</label>
                        <select name="status" id="editAssignmentStatus" 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <?php foreach ($assignmentStatuses as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Notes</label>
                        <textarea name="notes" id="editAssignmentNotes" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Additional notes about this assignment..."></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideEditAssignmentModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Update Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAssignmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg">
                <div>
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Confirm Delete
                    </h3>
                </div>
            </div>
            
            <div class="p-6">
                <p class="text-grey-700 mb-4">Are you sure you want to delete this assignment?</p>
                <p class="text-sm text-grey-500 mb-6">This action cannot be undone.</p>
                
                <div class="flex justify-end space-x-3">
                    <button onclick="hideDeleteAssignmentModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button onclick="confirmDeleteAssignment()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-trash mr-2"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="hideErrorModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="bg-red-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Error
                    </h3>
                    <button onclick="hideErrorModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <p id="errorMessage" class="text-gray-700 mb-6">An error occurred.</p>
                
                <div class="flex justify-end">
                    <button onclick="hideErrorModal()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-check mr-2"></i>
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize assignments data from PHP
const assignmentsData = <?= json_encode($assignments, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<!-- Load MVC Architecture for Assignment Management -->
<!-- Services -->
<script src="/js/services/APIService.js"></script>
<script src="/js/services/AssignmentManagementService.js"></script>

<!-- Models -->
<script src="/js/models/Assignment.js"></script>

<!-- Views -->
<script src="/js/views/AssignmentManagementView.js"></script>

<!-- Controllers -->
<script src="/js/controllers/AssignmentManagementController.js"></script>

<!-- Initialize MVC and expose global functions -->
<script src="/js/manage-assignments-mvc.js"></script>