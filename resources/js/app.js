import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Only start Alpine if Livewire is not present on the page.
// Livewire 3 will automatically boot and start Alpine if it is present.
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Livewire) {
        Alpine.start();
    }
});

// Hide brand user info when sidebar collapses
document.addEventListener('DOMContentLoaded', function() {
    const userInfoElement = document.querySelector('[data-hide-on-collapse="true"]');
    
    if (userInfoElement) {
        // Initial check
        updateUserInfoVisibility();
        
        // Watch for sidebar collapse
        const observer = new MutationObserver(function() {
            updateUserInfoVisibility();
        });
        
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
});

function updateUserInfoVisibility() {
    const body = document.body;
    const userInfoElement = document.querySelector('[data-hide-on-collapse="true"]');
    
    if (userInfoElement) {
        if (body.classList.contains('sidebar-collapse')) {
            userInfoElement.style.display = 'none !important';
            userInfoElement.style.visibility = 'hidden';
            userInfoElement.style.width = '0';
            userInfoElement.style.height = '0';
            userInfoElement.style.overflow = 'hidden';
            userInfoElement.style.position = 'absolute';
            userInfoElement.style.margin = '0';
            userInfoElement.style.padding = '0';
        } else {
            userInfoElement.style.display = '';
            userInfoElement.style.visibility = 'visible';
            userInfoElement.style.width = 'auto';
            userInfoElement.style.height = 'auto';
            userInfoElement.style.overflow = 'visible';
            userInfoElement.style.position = 'static';
            userInfoElement.style.margin = '';
            userInfoElement.style.padding = '';
        }
    }
}
