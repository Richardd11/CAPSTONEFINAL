/**
 * DEBUG EXPORT SELECTION
 * Run this in console when export modal is open
 */

console.log('🔍 Debugging Export Selection\n');

// Check if modal is open
const modal = document.getElementById('exportDashboardModal');
const isOpen = modal && !modal.classList.contains('hidden');
console.log('1️⃣ Modal Status:', isOpen ? '✅ Open' : '❌ Closed');

if (!isOpen) {
    console.log('⚠️ Please open the export modal first (click Export Data button)');
} else {
    // Check checkboxes
    const checkboxes = document.querySelectorAll('.exam-checkbox');
    console.log('\n2️⃣ Checkboxes Found:', checkboxes.length);
    
    if (checkboxes.length === 0) {
        console.log('❌ No checkboxes found! Exams may not have loaded.');
    } else {
        console.log('\n3️⃣ Checkbox Status:');
        checkboxes.forEach((cb, index) => {
            const examId = cb.id.replace('exam-', '');
            const isDisabled = cb.disabled;
            const isChecked = cb.checked;
            const studentCount = document.getElementById(`student-count-${examId}`)?.textContent || 'Unknown';
            
            console.log(`   Exam ${examId}:`);
            console.log(`      Disabled: ${isDisabled ? '❌ YES (no results)' : '✅ NO'}`);
            console.log(`      Checked: ${isChecked ? '✅ YES' : '⬜ NO'}`);
            console.log(`      Students: ${studentCount}`);
        });
        
        // Check if any are enabled
        const enabledCount = Array.from(checkboxes).filter(cb => !cb.disabled).length;
        console.log(`\n4️⃣ Enabled Checkboxes: ${enabledCount} of ${checkboxes.length}`);
        
        if (enabledCount === 0) {
            console.log('⚠️ All checkboxes are disabled because no exams have student results yet.');
            console.log('💡 Students need to take exams before you can export results.');
        }
    }
    
    // Check if function exists
    console.log('\n5️⃣ Function Check:');
    console.log('   toggleExamSelection:', typeof toggleExamSelection === 'function' ? '✅' : '❌');
    console.log('   selectedExams:', typeof selectedExams !== 'undefined' ? '✅' : '❌');
    
    // Try to manually select one
    const firstEnabled = document.querySelector('.exam-checkbox:not(:disabled)');
    if (firstEnabled) {
        console.log('\n6️⃣ Manual Test:');
        console.log('   Found enabled checkbox, trying to check it...');
        firstEnabled.checked = true;
        
        // Trigger the onchange event
        const examId = parseInt(firstEnabled.id.replace('exam-', ''));
        toggleExamSelection(examId);
        
        console.log('   Selected exams:', Array.from(selectedExams));
        console.log('   ✅ If you see exam ID above, selection is working!');
    } else {
        console.log('\n6️⃣ Manual Test: ⏭️ SKIPPED (no enabled checkboxes)');
    }
}

console.log('\n' + '='.repeat(60));
console.log('📊 DIAGNOSIS COMPLETE');
console.log('='.repeat(60));
