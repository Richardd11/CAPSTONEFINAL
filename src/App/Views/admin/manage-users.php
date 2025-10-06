<!-- Top Section - Add User Actions -->
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h4 class="text-xl font-semibold text-grey-800 mb-1">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Users
            </h4>
            <p class="text-grey-600">Add students and faculty to the system</p>
        </div>
        <div class="flex space-x-3">
            <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 transform hover:-translate-y-1" onclick="showAddStudentModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Student
            </button>
            <button class="bg-transparent border-2 border-grey-500 text-grey-600 hover:bg-grey-500 hover:text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300" onclick="showAddFacultyModal()">
                <i class="fas fa-plus mr-2"></i>
                Add Faculty
            </button>
        </div>
    </div>
</div>

<!-- Users Sub-tabs -->
<div class="mb-6">
    <div class="flex space-x-2">
        <button id="students-subtab-btn" class="px-4 py-2 rounded-lg bg-primary-600 text-white font-semibold" onclick="showUsersSubtab('students')">
            <i class="fas fa-user-graduate mr-2"></i>Students
        </button>
        <button id="faculty-subtab-btn" class="px-4 py-2 rounded-lg bg-white text-grey-700 border border-grey-300 hover:bg-grey-50" onclick="showUsersSubtab('faculty')">
            <i class="fas fa-chalkboard-teacher mr-2"></i>Faculty
        </button>
    </div>
</div>

<!-- Students Section - Organized by Year & Section -->
<div id="students-subtab" class="mb-8">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-graduation-cap mr-2 text-primary-600"></i>
        Students by Year & Section
    </h5>
    
    <!-- Year-Section Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <?php 
        $firstSection = true;
        foreach ($yearSections as $yearSection => $count): 
        ?>
            <button class="year-section-tab <?= $firstSection ? 'active' : '' ?> bg-grey-100 border border-grey-300 text-grey-600 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-300 hover:bg-primary-600 hover:text-white hover:border-primary-600" 
                    data-section="section-<?= str_replace(' ', '-', strtolower($yearSection)) ?>">
                <?= $yearSection ?>
                <span class="bg-green-500 text-white rounded-full w-5 h-5 inline-flex items-center justify-center text-xs font-bold ml-2"><?= $count ?></span>
            </button>
        <?php 
            $firstSection = false;
        endforeach; 
        ?>
    </div>

    <!-- Student Sections Content -->
    <?php 
    $firstSection = true;
    foreach ($yearSections as $yearSection => $count): 
        $sectionId = 'section-' . str_replace(' ', '-', strtolower($yearSection));
        $sectionStudents = array_filter($students, function($student) use ($yearSection) {
            return ($student['year_level'] . ' ' . $student['section']) === $yearSection;
        });
    ?>
        <div class="student-section <?= $firstSection ? 'active' : '' ?> <?= !$firstSection ? 'hidden' : '' ?>" 
             id="<?= $sectionId ?>">
            
            <!-- Section Header -->
            <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-lg font-bold text-primary-600">
                            <i class="fas fa-users mr-2"></i>
                            <?= $yearSection ?>
                        </h6>
                    </div>
                    <div>
                        <span class="text-grey-600 text-sm"><?= $count ?> students</span>
                    </div>
                </div>
            </div>

            <!-- Student Cards -->
            <?php foreach ($sectionStudents as $student): ?>
                <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="text-lg font-bold text-primary-600 mb-2">
                                <i class="fas fa-user-graduate mr-2"></i>
                                <?= htmlspecialchars($student['full_name']) ?>
                            </div>
                            <div class="text-grey-600 text-sm mb-2">
                                <i class="fas fa-id-card mr-2"></i>
                                <?= htmlspecialchars($student['school_id']) ?>
                            </div>
                            <div class="text-grey-500 text-xs">
                                <i class="fas fa-calendar mr-2"></i>
                                Added on <?= date('M d, Y', strtotime($student['created_at'])) ?>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editStudent(<?= $student['user_id'] ?>)">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </button>
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteStudent(<?= $student['user_id'] ?>)">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php 
        $firstSection = false;
    endforeach; 
    ?>
</div>

<!-- Faculty Section -->
<div id="faculty-subtab" class="mt-12 hidden">
    <h5 class="text-lg font-semibold text-grey-800 mb-4">
        <i class="fas fa-chalkboard-teacher mr-2 text-primary-600"></i>
        Faculty Members
    </h5>
    
    <div class="bg-grey-100 p-4 rounded-lg mb-4 border-l-4 border-primary-600">
        <div class="flex justify-between items-center">
            <div>
                <h6 class="text-lg font-bold text-primary-600">
                    <i class="fas fa-users mr-2"></i>
                    All Faculty
                </h6>
            </div>
            <div>
                <span class="text-grey-600 text-sm"><?= count($faculty) ?> faculty members</span>
            </div>
        </div>
    </div>

    <!-- Faculty Cards -->
    <?php foreach ($faculty as $facultyMember): ?>
        <div class="bg-white border border-grey-200 rounded-lg p-6 mb-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <div class="text-lg font-bold text-primary-600 mb-2">
                        <i class="fas fa-user-tie mr-2"></i>
                        <?= htmlspecialchars($facultyMember['full_name']) ?>
                    </div>
                    <div class="text-grey-600 text-sm mb-2">
                        <i class="fas fa-id-card mr-2"></i>
                        <?= htmlspecialchars($facultyMember['school_id']) ?>
                    </div>
                    <div class="text-grey-500 text-xs">
                        <i class="fas fa-calendar mr-2"></i>
                        Added on <?= date('M d, Y', strtotime($facultyMember['created_at'])) ?>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="editFaculty(<?= $facultyMember['user_id'] ?>)">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </button>
                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm transition-all duration-300" onclick="deleteFaculty(<?= $facultyMember['user_id'] ?>)">
                        <i class="fas fa-trash mr-1"></i>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Initialize user data from PHP
const studentsData = <?= json_encode($students ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const facultyData = <?= json_encode($faculty ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>


<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Student
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editStudentForm" action="/admin/users/edit-student" method="POST">
                <input type="hidden" id="editStudentId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="edit_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="edit_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="edit_year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="edit_section" class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select id="edit_section" name="section" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editStudentModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Update Student
            </button>
        </div>
    </div>
</div>


<!-- Delete Faculty Confirmation Modal -->
<div id="deleteFacultyModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-grey-200">
                <h3 class="text-lg font-semibold text-grey-800">Confirm Delete</h3>
            </div>
            <div class="p-6">
                <p class="text-grey-700 mb-6">Are you sure you want to delete this faculty member?</p>
                <div class="flex justify-end space-x-3">
                    <button class="px-5 py-2 rounded-lg bg-grey-100 text-grey-700 hover:bg-grey-200" onclick="cancelDeleteFaculty()">Cancel</button>
                    <button class="px-5 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700" onclick="confirmDeleteFaculty()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Student Confirmation Modal -->
<div id="deleteStudentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-grey-200">
                <h3 class="text-lg font-semibold text-grey-800">Confirm Delete</h3>
            </div>
            <div class="p-6">
                <p class="text-grey-700 mb-6">Are you sure you want to delete this student?</p>
                <div class="flex justify-end space-x-3">
                    <button class="px-5 py-2 rounded-lg bg-grey-100 text-grey-700 hover:bg-grey-200" onclick="cancelDeleteStudent()">Cancel</button>
                    <button class="px-5 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700" onclick="confirmDeleteStudent()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Student
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addStudentForm" action="/admin/users/add-student" method="POST" onsubmit="handleFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-grey-700 mb-2">Year Level *</label>
                        <select id="year_level" name="year_level" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Year Level</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label for="section" class="block text-sm font-medium text-grey-700 mb-2">Section *</label>
                        <select id="section" name="section" required 
                                class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-grey-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-grey-500 mt-1">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="student">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('addStudentModal')" 
                    class="px-4 py-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                Cancel
            </button>
            <button onclick="submitForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Add Student
            </button>
        </div>
    </div>
</div>


<!-- Edit Faculty Modal -->
<div id="editFacultyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-edit mr-2 text-primary-600"></i>
                Edit Faculty
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="editFacultyForm" action="/admin/users/edit-faculty" method="POST">
                <input type="hidden" id="editFacultyId" name="user_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_faculty_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="edit_faculty_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="edit_faculty_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="edit_faculty_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>

                <input type="hidden" name="role" value="faculty">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('editFacultyModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitEditFacultyForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Update Faculty
            </button>
        </div>
    </div>
</div>


<!-- Add Faculty Modal -->
<div id="addFacultyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-grey-200">
            <h3 class="text-xl font-semibold text-grey-800">
                <i class="fas fa-user-plus mr-2 text-primary-600"></i>
                Add New Faculty
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <form id="addFacultyForm" action="/admin/users/add-faculty" method="POST" onsubmit="handleFacultyFormSubmit(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="faculty_school_id" class="block text-sm font-medium text-grey-700 mb-2">School ID *</label>
                        <input type="text" id="faculty_school_id" name="school_id" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="faculty_full_name" class="block text-sm font-medium text-grey-700 mb-2">Full Name *</label>
                        <input type="text" id="faculty_full_name" name="full_name" required 
                               class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="faculty_password" class="block text-sm font-medium text-grey-700 mb-2">Password</label>
                    <input type="password" id="faculty_password" name="password" 
                           placeholder="Leave blank for default password"
                           class="w-full px-3 py-2 border border-grey-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-grey-500 mt-1">Default password will be: School ID + Full Name</p>
                </div>

                <input type="hidden" name="role" value="faculty">
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t border-grey-200">
            <button onclick="closeModal('addFacultyModal')" 
                    class="px-4 py-2 text-grey-600 bg-grey-100 hover:bg-grey-200 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </button>
            <button onclick="submitFacultyForm()" 
                    class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                Add Faculty
            </button>
        </div>
    </div>
</div>


<!-- Load MVC Architecture for User Management -->
<!-- Services -->
<script src="/js/services/APIService.js"></script>
<script src="/js/services/UserManagementService.js"></script>

<!-- Models -->
<script src="/js/models/User.js"></script>

<!-- Controllers -->
<script src="/js/controllers/ManageUsersController.js"></script>

<!-- Initialize MVC and expose global functions -->
<script src="/js/manage-users-mvc.js"></script>