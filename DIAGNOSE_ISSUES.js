/**
 * DIAGNOSTIC SCRIPT - Run this in browser console on the broken page
 * This will tell you exactly what's missing
 */

console.log('🔍 FACULTY SYSTEM DIAGNOSTIC\n');
console.log('Current Page:', window.location.pathname);
console.log('='.repeat(60));

// Detect which page we're on
const page = window.location.pathname;
let pageName = 'Unknown';
if (page.includes('dashboard')) pageName = 'Dashboard';
if (page.includes('exams')) pageName = 'Exams';
if (page.includes('students')) pageName = 'Students';
if (page.includes('exam-results')) pageName = 'Exam Results';

console.log(`\n📄 Testing ${pageName} Page\n`);

// Test Dashboard
if (page.includes('dashboard')) {
    console.log('Dashboard Functions Check:');
    console.log('  showSubjectDetails:', typeof showSubjectDetails === 'function' ? '✅' : '❌ MISSING');
    console.log('  closeSubjectModal:', typeof closeSubjectModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  viewSubjectStudents:', typeof viewSubjectStudents === 'function' ? '✅' : '❌ MISSING');
    console.log('  viewSubjectScores:', typeof viewSubjectScores === 'function' ? '✅' : '❌ MISSING');
    console.log('  exportAllData:', typeof exportAllData === 'function' ? '✅' : '❌ MISSING');
    console.log('  loadExamsForExport:', typeof loadExamsForExport === 'function' ? '✅' : '❌ MISSING');
    console.log('  exportSingleExamData:', typeof exportSingleExamData === 'function' ? '✅' : '❌ MISSING');
    console.log('  selectAllExams:', typeof selectAllExams === 'function' ? '✅' : '❌ MISSING');
    console.log('  deselectAllExams:', typeof deselectAllExams === 'function' ? '✅' : '❌ MISSING');
    console.log('  exportSelectedExams:', typeof exportSelectedExams === 'function' ? '✅' : '❌ MISSING');
    console.log('  closeExportDashboard:', typeof closeExportDashboard === 'function' ? '✅' : '❌ MISSING');
    console.log('  openLogoutModal:', typeof openLogoutModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  closeLogoutModal:', typeof closeLogoutModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  confirmLogout:', typeof confirmLogout === 'function' ? '✅' : '❌ MISSING');
    console.log('  facultyDashboard:', typeof facultyDashboard !== 'undefined' ? '✅' : '❌ MISSING');
}

// Test Exams
if (page.includes('exams') && !page.includes('exam-results')) {
    console.log('Exams Page Functions Check:');
    console.log('  toggleDropdown:', typeof toggleDropdown === 'function' ? '✅' : '❌ MISSING');
    console.log('  deleteExam:', typeof deleteExam === 'function' ? '✅' : '❌ MISSING');
    console.log('  closeDeleteModal:', typeof closeDeleteModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  confirmDelete:', typeof confirmDelete === 'function' ? '✅' : '❌ MISSING');
    console.log('  facultyExams:', typeof facultyExams !== 'undefined' ? '✅' : '❌ MISSING');
}

// Test Students
if (page.includes('students')) {
    console.log('Students Page Functions Check:');
    console.log('  toggleGroup:', typeof toggleGroup === 'function' ? '✅' : '❌ MISSING');
    console.log('  exportSection:', typeof exportSection === 'function' ? '✅' : '❌ MISSING');
    console.log('  facultyStudents:', typeof facultyStudents !== 'undefined' ? '✅' : '❌ MISSING');
}

// Test Exam Results
if (page.includes('exam-results')) {
    console.log('Exam Results Functions Check:');
    console.log('  selectExam:', typeof selectExam === 'function' ? '✅' : '❌ MISSING');
    console.log('  viewDetails:', typeof viewDetails === 'function' ? '✅' : '❌ MISSING');
    console.log('  closeDetailsModal:', typeof closeDetailsModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  exportExamResults:', typeof exportExamResults === 'function' ? '✅' : '❌ MISSING');
    console.log('  showOverrideModal:', typeof window.showOverrideModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  closeOverrideModal:', typeof window.closeOverrideModal === 'function' ? '✅' : '❌ MISSING');
    console.log('  submitOverride:', typeof window.submitOverride === 'function' ? '✅' : '❌ MISSING');
    console.log('  facultyExamResults:', typeof facultyExamResults !== 'undefined' ? '✅' : '❌ MISSING');
    
    console.log('\nMVC Classes Check:');
    console.log('  FacultyExamResultsService:', typeof FacultyExamResultsService !== 'undefined' ? '✅' : '❌ MISSING');
    console.log('  FacultyExamResultsView:', typeof FacultyExamResultsView !== 'undefined' ? '✅' : '❌ MISSING');
    console.log('  StudentDetailsRenderer:', typeof StudentDetailsRenderer !== 'undefined' ? '✅' : '❌ MISSING');
    console.log('  ExamResult:', typeof ExamResult !== 'undefined' ? '✅' : '❌ MISSING');
}

// Check for JavaScript errors
console.log('\n❗ JavaScript Errors:');
const errors = window.jsErrors || [];
if (errors.length > 0) {
    errors.forEach(err => console.error('  ❌', err));
} else {
    console.log('  ✅ No errors captured');
}

// Check loaded scripts
console.log('\n📦 Loaded Scripts:');
const scripts = Array.from(document.querySelectorAll('script[src]'));
scripts.forEach(script => {
    const src = script.src.replace(window.location.origin, '');
    console.log('  ', src);
});

console.log('\n' + '='.repeat(60));
console.log('💡 INSTRUCTIONS:');
console.log('1. Look for ❌ MISSING functions above');
console.log('2. Check browser Console tab for red errors');
console.log('3. Check Network tab for failed script loads (red)');
console.log('4. Share the ❌ MISSING items with me to fix!');
console.log('='.repeat(60));
