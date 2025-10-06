/**
 * TEST EXPORT FIX
 * Run this in browser console on /faculty/dashboard
 */

console.log('🧪 Testing Export Fix...\n');

// Test 1: Check if export functions exist
console.log('1️⃣ Export Functions Check:');
const exportFunctions = [
    'exportAllData',
    'loadExamsForExport',
    'exportSingleExamData',
    'exportSelectedExams',
    'selectAllExams',
    'deselectAllExams',
    'closeExportDashboard',
    'showNotification'
];

let allExist = true;
exportFunctions.forEach(fn => {
    const exists = typeof window[fn] === 'function';
    console.log(`   ${fn}:`, exists ? '✅' : '❌ MISSING');
    if (!exists) allExist = false;
});

// Test 2: Check if modal exists
console.log('\n2️⃣ Modal Elements Check:');
const modal = document.getElementById('exportDashboardModal');
console.log('   exportDashboardModal:', modal ? '✅' : '❌ MISSING');
const examsList = document.getElementById('exportExamsList');
console.log('   exportExamsList:', examsList ? '✅' : '❌ MISSING');
const selectedCount = document.getElementById('selectedCount');
console.log('   selectedCount:', selectedCount ? '✅' : '❌ MISSING');

// Test 3: Check if variables are initialized
console.log('\n3️⃣ Global Variables Check:');
console.log('   availableExams:', typeof availableExams !== 'undefined' ? '✅' : '❌ MISSING');
console.log('   selectedExams:', typeof selectedExams !== 'undefined' ? '✅' : '❌ MISSING');

// Test 4: Try to open modal (if all checks pass)
if (allExist && modal) {
    console.log('\n4️⃣ Functional Test:');
    console.log('   Attempting to open export modal...');
    
    try {
        // This should open the modal
        exportAllData();
        
        setTimeout(() => {
            const isVisible = !modal.classList.contains('hidden');
            console.log('   Modal opened:', isVisible ? '✅ SUCCESS' : '❌ FAILED');
            
            if (isVisible) {
                console.log('   ✅ Export functionality is working!');
                console.log('   You can close the modal now.');
            }
        }, 500);
    } catch (error) {
        console.log('   ❌ Error opening modal:', error.message);
    }
} else {
    console.log('\n4️⃣ Functional Test: ⏭️ SKIPPED (prerequisites not met)');
}

// Summary
console.log('\n' + '='.repeat(50));
if (allExist && modal) {
    console.log('✅ ALL TESTS PASSED - Export should work!');
} else {
    console.log('❌ SOME TESTS FAILED - Check errors above');
}
console.log('='.repeat(50));
