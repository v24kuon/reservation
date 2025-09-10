import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Confirm dialog functionality
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const confirmMessage = form.getAttribute('data-confirm');

        if (confirmMessage && !confirm(confirmMessage)) {
            e.preventDefault();
        }
    });
});

Alpine.start();
