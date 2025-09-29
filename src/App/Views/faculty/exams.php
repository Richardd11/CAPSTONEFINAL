<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Exams - Faculty Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .exam-card {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
        
        .exam-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        /* Ultra-Smooth Delete Modal Animations */
        #deleteModal .relative {
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            will-change: transform, opacity, filter;
        }

        #deleteModal .fixed.inset-0 {
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Modern button animations for delete modal */
        #deleteModal button {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateZ(0);
            will-change: transform, box-shadow;
        }

        #deleteModal button:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }

        #deleteModal button:active {
            transform: translateY(0) scale(0.95);
            transition: all 0.1s ease;
        }

        /* Floating animation for particles */
        #deleteModal .animate-ping {
            animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        #deleteModal .animate-pulse {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .header-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-gradient text-white py-8 mb-8">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/dashboard" 
                       class="mr-6 p-2 rounded-full hover:bg-white/20 transition-all duration-300 group">
                        <i class="fas fa-arrow-left text-xl group-hover:transform group-hover:-translate-x-1 transition-transform"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold mb-2 tracking-tight">
                            <i class="fas fa-file-alt mr-3 text-white/90"></i>
                            My Exams
                        </h1>
                        <p class="text-white/80 text-lg">Manage and view your created exams</p>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam" 
                       class="btn-primary text-white px-8 py-3 rounded-xl font-semibold flex items-center">
                        <i class="fas fa-plus mr-2"></i>Create New Exam
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-6 max-w-6xl">
        <?php if (empty($exams)): ?>
            <!-- No Exams State -->
            <div class="glass-card rounded-2xl p-12 text-center">
                <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-file-alt text-4xl text-blue-600"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-4">No Exams Created Yet</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Start creating your first exam to assess your students' knowledge and track their progress.
                </p>
                <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/create-exam" 
                   class="btn-primary text-white px-8 py-4 rounded-xl font-semibold inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Create Your First Exam
                </a>
            </div>
        <?php else: ?>
            <!-- Exams by Year Level -->
            <?php 
            // Group exams by year level
            $examsByYear = [];
            foreach ($exams as $exam) {
                $yearLevel = $exam->getYearLevel() ?? 'Unassigned';
                if (!isset($examsByYear[$yearLevel])) {
                    $examsByYear[$yearLevel] = [];
                }
                $examsByYear[$yearLevel][] = $exam;
            }
            
            // Sort year levels
            $yearOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year', 'Unassigned'];
            uksort($examsByYear, function($a, $b) use ($yearOrder) {
                $aPos = array_search($a, $yearOrder);
                $bPos = array_search($b, $yearOrder);
                if ($aPos === false) $aPos = 999;
                if ($bPos === false) $bPos = 999;
                return $aPos - $bPos;
            });
            ?>
            
            <?php foreach ($examsByYear as $yearLevel => $yearExams): ?>
                <!-- Year Level Section -->
                <div class="mb-12">
                    <!-- Year Level Header -->
                    <div class="glass-card p-6 mb-6 rounded-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-4">
                                    <i class="fas fa-graduation-cap text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($yearLevel) ?></h3>
                                    <p class="text-gray-600">Exams & Assessments</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="bg-indigo-100 text-indigo-800 px-4 py-2 rounded-full">
                                    <span class="font-semibold"><?= count($yearExams) ?></span>
                                    <span class="text-sm">Exam<?= count($yearExams) !== 1 ? 's' : '' ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Exams Grid for this Year Level -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($yearExams as $exam): ?>
                    <div class="exam-card rounded-2xl p-6 border border-gray-200">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                                    <?= htmlspecialchars($exam->getTitle()) ?>
                                </h3>
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= ucfirst(htmlspecialchars($exam->getExamType())) ?>
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?= $exam->getIsActive() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $exam->getIsActive() ? 'Active' : 'Inactive' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="relative">
                                <button class="text-gray-400 hover:text-gray-600 p-2" onclick="toggleDropdown('<?= $exam->getId() ?>')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div id="dropdown-<?= $exam->getId() ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border z-10">
                                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/<?= $exam->getId() ?>" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-lg">
                                        <i class="fas fa-eye mr-2"></i>View Details
                                    </a>
                                    <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/<?= $exam->getId() ?>/edit" 
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-edit mr-2"></i>Edit Exam
                                    </a>
                                    <button onclick="deleteExam('<?= $exam->getId() ?>')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-lg">
                                        <i class="fas fa-trash mr-2"></i>Delete Exam
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($exam->getDescription()): ?>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                <?= htmlspecialchars($exam->getDescription()) ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock mr-2 text-blue-500"></i>
                                <?= $exam->getTimeLimit() ?> minutes
                            </div>
                            <?php if ($exam->getStartDate()): ?>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-2 text-green-500"></i>
                                    <?= date('M j, Y g:i A', strtotime($exam->getStartDate())) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-question-circle mr-2 text-purple-500"></i>
                                <?php 
                                $questionCount = 0;
                                try {
                                    $questionCount = count($exam->getQuestions() ?? []);
                                } catch (Exception $e) {
                                    $questionCount = 0;
                                }
                                echo $questionCount;
                                ?> questions
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/<?= $exam->getId() ?>" 
                               class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors text-center">
                                View Details
                            </a>
                            <a href="<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/<?= $exam->getId() ?>/edit" 
                               class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors text-center">
                                Edit
                            </a>
                        </div>
                    </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-[100] hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/0 backdrop-blur-0 transition-all duration-500 ease-out"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 flex items-center justify-center p-6">
            <div class="relative bg-gradient-to-br from-white via-white to-red-50/30 rounded-3xl shadow-2xl max-w-md w-full mx-4 p-0 transform transition-all duration-500 ease-[cubic-bezier(0.34,1.56,0.64,1)] scale-75 opacity-0 translate-y-12 rotate-1" 
                 style="box-shadow: 0 32px 64px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.3);">
                
                <!-- Decorative background pattern -->
                <div class="absolute inset-0 rounded-3xl overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-red-400/10 to-orange-400/10 rounded-full blur-2xl transform translate-x-8 -translate-y-8"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-red-400/10 to-pink-400/10 rounded-full blur-xl transform -translate-x-4 translate-y-4"></div>
                </div>
                
                <!-- Content -->
                <div class="relative p-8">
                    <!-- Header with animated icon -->
                    <div class="text-center mb-8">
                        <div class="relative w-20 h-20 mx-auto mb-6">
                            <div class="absolute inset-0 bg-gradient-to-br from-red-400 to-red-500 rounded-2xl shadow-lg transform rotate-3"></div>
                            <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center transform -rotate-3 transition-transform duration-500 hover:rotate-0">
                                <i class="fas fa-exclamation-triangle text-white text-3xl"></i>
                            </div>
                            <!-- Floating particles -->
                            <div class="absolute -top-2 -right-2 w-3 h-3 bg-red-400 rounded-full animate-ping"></div>
                            <div class="absolute -bottom-1 -left-1 w-2 h-2 bg-red-400 rounded-full animate-pulse"></div>
                        </div>
                        
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-red-600 to-red-800 bg-clip-text text-transparent mb-3">
                            Delete Exam
                        </h3>
                        <p class="text-slate-600 leading-relaxed mb-3">
                            Are you sure you want to delete this exam? This action cannot be undone and will permanently remove:
                        </p>
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-4">
                            <ul class="text-sm text-red-700 space-y-1">
                                <li><i class="fas fa-times-circle mr-2"></i>All exam questions</li>
                                <li><i class="fas fa-times-circle mr-2"></i>Student submissions</li>
                                <li><i class="fas fa-times-circle mr-2"></i>Exam results & scores</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="flex space-x-4">
                        <button onclick="closeDeleteModal()" 
                                class="flex-1 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-700 py-4 px-6 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 active:scale-95 hover:shadow-lg">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </button>
                        <button onclick="confirmDelete()" 
                                class="flex-1 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 active:from-red-700 active:to-red-800 text-white py-4 px-6 rounded-2xl font-semibold transition-all duration-200 transform hover:scale-105 active:scale-95 hover:shadow-lg shadow-red-500/25">
                            <i class="fas fa-trash mr-2"></i>
                            <span id="deleteButtonText">Delete Exam</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(examId) {
            const dropdown = document.getElementById(`dropdown-${examId}`);
            // Close all other dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                if (d.id !== `dropdown-${examId}`) {
                    d.classList.add('hidden');
                }
            });
            dropdown.classList.toggle('hidden');
        }

        let examToDelete = null;

        function deleteExam(examId) {
            examToDelete = examId;
            showDeleteModal();
        }

        function showDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const modalContent = modal.querySelector('.relative');
            const backdrop = modal.querySelector('.fixed.inset-0');
            
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

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const modalContent = modal.querySelector('.relative');
            const backdrop = modal.querySelector('.fixed.inset-0');
            
            // Ultra-smooth exit animation
            modalContent.style.transform = 'scale(0.8) translateY(30px) rotate(-2deg)';
            modalContent.style.opacity = '0';
            modalContent.style.filter = 'blur(4px)';
            
            // Fade out backdrop
            backdrop.style.background = 'rgba(0, 0, 0, 0)';
            backdrop.style.backdropFilter = 'blur(0px)';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
                examToDelete = null;
                
                // Reset for next time
                modalContent.style.transform = 'scale(0.75) translateY(48px) rotate(1deg)';
                modalContent.style.opacity = '0';
                modalContent.style.filter = 'blur(0px)';
                
                // Reset button state
                const deleteBtn = document.getElementById('deleteButtonText');
                deleteBtn.innerHTML = 'Delete Exam';
                deleteBtn.parentElement.disabled = false;
            }, 500);
        }

        function confirmDelete() {
            if (!examToDelete) return;
            
            const deleteBtn = document.getElementById('deleteButtonText');
            const deleteButton = deleteBtn.parentElement;
            
            // Show loading state
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';
            deleteButton.disabled = true;
            deleteButton.style.opacity = '0.7';
            
            fetch(`<?= dirname($_SERVER['SCRIPT_NAME']) ?>/faculty/exam/${examToDelete}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success state
                    deleteBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Deleted!';
                    deleteButton.style.background = 'linear-gradient(to right, #10b981, #059669)';
                    
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast('Failed to delete exam: ' + data.message, 'error');
                    closeDeleteModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while deleting the exam', 'error');
                closeDeleteModal();
            });
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-4 rounded-2xl shadow-lg z-50 transform translate-x-full transition-all duration-300 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>
                    ${message}
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[onclick^="toggleDropdown"]')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                    d.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>
