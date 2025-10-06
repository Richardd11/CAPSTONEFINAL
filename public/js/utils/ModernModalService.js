/**
 * ModernModalService - Modern modal dialog system
 * Provides success, error, warning, info, and confirm dialogs
 */
class ModernModalService {
    constructor() {
        this.currentModal = null;
        this.modalContainer = null;
        this.initializeContainer();
    }

    initializeContainer() {
        if (!document.getElementById('modernModalContainer')) {
            const container = document.createElement('div');
            container.id = 'modernModalContainer';
            document.body.appendChild(container);
            this.modalContainer = container;
        } else {
            this.modalContainer = document.getElementById('modernModalContainer');
        }
    }

    success(title, message) {
        return this.show({
            type: 'success',
            title: title,
            message: message,
            icon: 'fas fa-check-circle',
            iconColor: 'text-green-600',
            bgColor: 'bg-green-100'
        });
    }

    error(title, message) {
        return this.show({
            type: 'error',
            title: title,
            message: message,
            icon: 'fas fa-exclamation-circle',
            iconColor: 'text-red-600',
            bgColor: 'bg-red-100'
        });
    }

    warning(title, message) {
        return this.show({
            type: 'warning',
            title: title,
            message: message,
            icon: 'fas fa-exclamation-triangle',
            iconColor: 'text-yellow-600',
            bgColor: 'bg-yellow-100'
        });
    }

    info(title, message) {
        return this.show({
            type: 'info',
            title: title,
            message: message,
            icon: 'fas fa-info-circle',
            iconColor: 'text-blue-600',
            bgColor: 'bg-blue-100'
        });
    }

    async confirm(title, message, options = {}) {
        return new Promise((resolve) => {
            const confirmText = options.confirmText || 'Confirm';
            const cancelText = options.cancelText || 'Cancel';
            const icon = options.icon || 'fas fa-question-circle';

            const modalHTML = `
                <div id="modernModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4">
                    <div id="modernModalContent" class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
                        <div class="text-center">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-6">
                                <i class="${icon} text-blue-600 text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">${this.escapeHtml(title)}</h3>
                            <p class="text-gray-600 mb-6 whitespace-pre-wrap">${this.escapeHtml(message)}</p>
                            <div class="flex space-x-3">
                                <button id="modalCancelBtn" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-all duration-200 font-semibold">
                                    ${this.escapeHtml(cancelText)}
                                </button>
                                <button id="modalConfirmBtn" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-200 font-semibold shadow-lg">
                                    ${this.escapeHtml(confirmText)}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            this.modalContainer.innerHTML = modalHTML;
            const modal = document.getElementById('modernModal');
            const modalContent = document.getElementById('modernModalContent');

            requestAnimationFrame(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            });

            document.getElementById('modalConfirmBtn').onclick = () => {
                this.closeModal(modal, modalContent);
                resolve(true);
            };

            document.getElementById('modalCancelBtn').onclick = () => {
                this.closeModal(modal, modalContent);
                resolve(false);
            };

            modal.onclick = (e) => {
                if (e.target === modal) {
                    this.closeModal(modal, modalContent);
                    resolve(false);
                }
            };

            this.currentModal = modal;
        });
    }

    show(options) {
        const modalHTML = `
            <div id="modernModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center p-4">
                <div id="modernModalContent" class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full ${options.bgColor} mb-6">
                            <i class="${options.icon} ${options.iconColor} text-3xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">${this.escapeHtml(options.title)}</h3>
                        <p class="text-gray-600 mb-6 whitespace-pre-wrap">${this.escapeHtml(options.message)}</p>
                        <button id="modalCloseBtn" class="w-full px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-200 font-semibold shadow-lg">
                            <i class="fas fa-check mr-2"></i>Okay
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.modalContainer.innerHTML = modalHTML;
        const modal = document.getElementById('modernModal');
        const modalContent = document.getElementById('modernModalContent');

        requestAnimationFrame(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        });

        document.getElementById('modalCloseBtn').onclick = () => {
            this.closeModal(modal, modalContent);
        };

        modal.onclick = (e) => {
            if (e.target === modal) {
                this.closeModal(modal, modalContent);
            }
        };

        if (options.type === 'success' || options.type === 'info') {
            setTimeout(() => {
                if (modal && modal.parentNode) {
                    this.closeModal(modal, modalContent);
                }
            }, 3000);
        }

        this.currentModal = modal;
    }

    closeModal(modal, modalContent) {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            if (modal && modal.parentNode) {
                modal.remove();
            }
            this.currentModal = null;
        }, 300);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    close() {
        if (this.currentModal) {
            const modalContent = this.currentModal.querySelector('#modernModalContent');
            this.closeModal(this.currentModal, modalContent);
        }
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModernModalService;
} else {
    window.modernModal = new ModernModalService();
    window.ModernModalService = ModernModalService;
}
