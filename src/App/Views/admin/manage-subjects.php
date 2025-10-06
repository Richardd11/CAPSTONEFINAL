<!-- Top Section - Add Subject Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-book mr-2 text-primary-600"></i>
                Add New Subjects
            </h4>
            <p class="text-grey-600">Add subjects to the system</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1" onclick="showAddSubjectModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Subject
            </button>
        </div>
    </div>
</div>



<!-- Subjects Section - Organized by Year & Semester -->
<div class="mb-8">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-book-open mr-2 text-primary-600"></i>
        Subjects by Year & Semester
    </h5>
    
    <!-- Year-Semester Tabs -->
    <div class="flex flex-wrap gap-2 mb-6" id="yearSemesterTabs">
        <!-- Tabs will be dynamically generated -->
    </div>

    <!-- Subjects Content -->
    <div id="subjectsContent">
        <!-- Content will be dynamically loaded -->
    </div>
</div>

<!-- Add Subject Modal -->
<div id="addSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-plus mr-2"></i>
                        Add New Subject
                    </h3>
                    <button onclick="hideAddSubjectModal()" class="text-white hover:text-grey-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="addSubjectForm" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Code *</label>
                        <input type="text" name="subject_code" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="e.g., CS101">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Name *</label>
                        <input type="text" name="subject_name" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="e.g., Introduction to Computer Science">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Subject description..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Units *</label>
                        <input type="number" name="units" required min="1" max="6" value="3"
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($yearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideAddSubjectModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Add Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="bg-primary-600 text-white px-6 py-4 rounded-t-lg">
                <div>
                    <h3 class="text-xl font-semibold">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Subject
                    </h3>
                </div>
            </div>
            
            <form id="editSubjectForm" class="p-6">
                <input type="hidden" name="subject_id" id="editSubjectId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Code *</label>
                        <input type="text" name="subject_code" id="editSubjectCode" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Subject Name *</label>
                        <input type="text" name="subject_name" id="editSubjectName" required 
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-grey-700 mb-2">Description</label>
                        <textarea name="description" id="editSubjectDescription" rows="3" 
                                  class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Units *</label>
                        <input type="number" name="units" id="editSubjectUnits" required min="1" max="6"
                               class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select name="year_level" id="editSubjectYearLevel" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Year Level</option>
                            <?php foreach ($yearLevels as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-grey-700 mb-2">Semester *</label>
                        <select name="semester" id="editSubjectSemester" required 
                                class="w-full px-4 py-2 border border-grey-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $key => $value): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="hideEditSubjectModal()" 
                            class="bg-grey-500 hover:bg-grey-600 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Update Subject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteSubjectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-grey-200">
                <h3 class="text-lg font-semibold text-grey-800">Confirm Delete</h3>
            </div>
            <div class="p-6">
                <p class="text-grey-700 mb-6">Are you sure you want to delete this subject?</p>
                <div class="flex justify-end space-x-3">
                    <button class="px-5 py-2 rounded-lg bg-grey-100 text-grey-700 hover:bg-grey-200" onclick="hideDeleteSubjectModal()">Cancel</button>
                    <button class="px-5 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700" onclick="confirmDeleteSubject()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize subjects data from PHP
const subjectsData = <?= json_encode($subjects, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<!-- Load MVC Architecture for Subject Management -->
<!-- Views -->
<script src="/js/views/SubjectManagementView.js"></script>

<!-- Controllers -->
<script src="/js/controllers/SubjectManagementController.js"></script>

<!-- Initialize MVC and expose global functions -->
<script src="/js/manage-subjects-mvc.js"></script>
