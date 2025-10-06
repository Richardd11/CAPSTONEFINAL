/**
 * QUICK TEST SCRIPT
 * Copy and paste this into browser console on /faculty/exam-results page
 */

console.log('🧪 Starting Quick Test Suite...\n');

// Test 1: Check JS Loading
console.log('📦 Test 1: JavaScript Loading');
console.log('   Service:', typeof FacultyExamResultsService !== 'undefined' ? '✅' : '❌');
console.log('   View:', typeof FacultyExamResultsView !== 'undefined' ? '✅' : '❌');
console.log('   Controller:', typeof FacultyExamResultsController !== 'undefined' ? '✅' : '❌');
console.log('   Renderer:', typeof StudentDetailsRenderer !== 'undefined' ? '✅' : '❌');
console.log('   Model:', typeof ExamResult !== 'undefined' ? '✅' : '❌');
console.log('   Instance:', typeof facultyExamResults !== 'undefined' ? '✅' : '❌');

// Test 2: Check Controller Structure
if (typeof facultyExamResults !== 'undefined') {
    console.log('\n🎮 Test 2: Controller Structure');
    console.log('   Has service:', !!facultyExamResults.service ? '✅' : '❌');
    console.log('   Has view:', !!facultyExamResults.view ? '✅' : '❌');
    console.log('   Has selectExam:', typeof facultyExamResults.selectExam === 'function' ? '✅' : '❌');
    console.log('   Has viewDetails:', typeof facultyExamResults.viewDetails === 'function' ? '✅' : '❌');
    console.log('   Has exportExamResults:', typeof facultyExamResults.exportExamResults === 'function' ? '✅' : '❌');
}

// Test 3: API Connectivity
console.log('\n🔌 Test 3: API Endpoints');
(async () => {
    try {
        const response = await fetch('/faculty/api/exams');
        console.log('   /faculty/api/exams:', response.status === 200 ? '✅ OK' : response.status === 401 ? '⚠️ Auth Required' : `❌ ${response.status}`);
        
        if (response.ok) {
            const data = await response.json();
            console.log('   Response format:', data.success ? '✅ Valid' : '❌ Invalid');
            console.log('   Exams count:', data.exams ? data.exams.length : 0);
        }
    } catch (error) {
        console.log('   API Error:', '❌', error.message);
    }
})();

// Test 4: Service Methods
if (typeof FacultyExamResultsService !== 'undefined') {
    console.log('\n⚙️ Test 4: Service Methods');
    const service = new FacultyExamResultsService();
    
    // Test grade calculation
    const grade = service.getGrade(85);
    console.log('   getGrade(85):', grade === 'B+' ? '✅ Correct (B+)' : `❌ Wrong (${grade})`);
    
    // Test statistics
    const testData = [{ score: 80 }, { score: 90 }, { score: 70 }];
    const stats = service.calculateStatistics(testData);
    console.log('   calculateStatistics:', stats.averageScore === 80 ? '✅ Correct (80)' : `❌ Wrong (${stats.averageScore})`);
    
    // Test grouping
    const testExams = [
        { subject: 'Math' },
        { subject: 'Math' },
        { subject: 'Science' }
    ];
    const grouped = service.groupExamsBySubject(testExams);
    console.log('   groupExamsBySubject:', Object.keys(grouped).length === 2 ? '✅ Correct (2 groups)' : `❌ Wrong`);
}

// Test 5: View Methods
if (typeof FacultyExamResultsView !== 'undefined' && typeof FacultyExamResultsService !== 'undefined') {
    console.log('\n🎨 Test 5: View Methods');
    const service = new FacultyExamResultsService();
    const view = new FacultyExamResultsView(service);
    
    console.log('   renderExamsList:', typeof view.renderExamsList === 'function' ? '✅' : '❌');
    console.log('   renderResults:', typeof view.renderResults === 'function' ? '✅' : '❌');
    console.log('   showToast:', typeof view.showToast === 'function' ? '✅' : '❌');
    console.log('   downloadCSV:', typeof view.downloadCSV === 'function' ? '✅' : '❌');
}

// Test 6: Global Functions
console.log('\n🌐 Test 6: Global Functions (Backward Compatibility)');
console.log('   selectExam:', typeof selectExam === 'function' ? '✅' : '❌');
console.log('   viewDetails:', typeof viewDetails === 'function' ? '✅' : '❌');
console.log('   exportExamResults:', typeof exportExamResults === 'function' ? '✅' : '❌');
console.log('   window.showOverrideModal:', typeof window.showOverrideModal === 'function' ? '✅' : '❌');
console.log('   window.submitOverride:', typeof window.submitOverride === 'function' ? '✅' : '❌');

// Summary
console.log('\n' + '='.repeat(50));
console.log('🎯 QUICK TEST COMPLETE');
console.log('='.repeat(50));
console.log('\nIf all tests show ✅, the system is ready!');
console.log('If you see ⚠️ Auth Required, login as faculty first.');
console.log('If you see ❌, check the error details above.\n');
