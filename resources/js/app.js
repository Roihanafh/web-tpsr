import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Livewire 3 boots Alpine automatically via @livewireScripts.
// Only start Alpine manually if Livewire is NOT on the page.
// We defer to 'livewire:init' event — if it fires, Livewire handles Alpine.
// Otherwise start Alpine ourselves after a short tick.
let alpineStarted = false;

document.addEventListener('livewire:init', () => {
    alpineStarted = true;
});

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        if (!alpineStarted && !window.__alpine_started) {
            window.__alpine_started = true;
            Alpine.start();
        }
    }, 0);
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
