const modalManager = {
    modals: [],
    init() {
        this.modals = document.querySelectorAll('.modal');
        this.modals.forEach(modal => {
            const modalId = modal.getAttribute('id');

            const openButtons = document.querySelectorAll(`[for="${modalId}"]`);
            openButtons.forEach(button => {
                button.addEventListener('click', () => this.openModal(modal));
            });

            const closeButton = modal.querySelector('.modal__close');
            if (closeButton) {
                closeButton.addEventListener('click', () => this.closeModal(modal));
            }
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });
    },
    openModal(modal) {
        if (modal) {
            modal.classList.remove('hidden');
        } else {
            console.error(`Modal with ID ${modalId} not found.`);
        }
    },
    closeModal(modal) {
        if (modal) {
            modal.classList.add('hidden');
        } else {
            console.error('Modal not found or already closed.');
        }
    },
}

document.addEventListener('DOMContentLoaded', modalManager.init.bind(modalManager));