// Shared Dashboard JavaScript Functions

// Logout Modal Functions
function openLogoutModal() {
    document.getElementById('logoutModal').classList.remove('hidden');
}

function closeLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
}

function confirmLogout() {
    const logoutBtn = document.querySelector('#logoutModal button[onclick="confirmLogout()"]');
    const originalText = logoutBtn.innerHTML;
    logoutBtn.disabled = true;
    logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Logging out...';
    
    // Determine the correct logout URL based on current path
    const currentPath = window.location.pathname;
    let logoutUrl = '';
    
    if (currentPath.includes('/admin/')) {
        logoutUrl = dirname(currentPath) + '/logout?confirm=true';
    } else if (currentPath.includes('/faculty/')) {
        logoutUrl = dirname(currentPath) + '/logout?confirm=true';
    } else if (currentPath.includes('/student/')) {
        logoutUrl = dirname(currentPath) + '/logout?confirm=true';
    } else {
        // Fallback
        logoutUrl = '/login';
    }
    
    // Simple redirect to logout (server handles the logout)
    setTimeout(() => {
        window.location.href = logoutUrl;
    }, 500);
}

// Helper function to get directory name from path
function dirname(path) {
    return path.substring(0, path.lastIndexOf('/'));
}

// Toast Notification Function
function showToast(message, type = 'success') {
    const existingToast = document.getElementById('toast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 300);
    }, 3000);
}

// Modal Close on Outside Click
document.addEventListener('click', function(e) {
    const modals = document.querySelectorAll('.modal-backdrop');
    modals.forEach(modal => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

// Escape Key to Close Modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const visibleModals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
        visibleModals.forEach(modal => {
            modal.classList.add('hidden');
        });
    }
});
