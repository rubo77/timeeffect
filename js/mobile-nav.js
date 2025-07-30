/**
 * Mobile Navigation Touch Fix
 * Handles user avatar dropdown on touch devices
 */

(function() {
    'use strict';
    
    // Detect mobile/touch devices
    const isMobile = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const userDropdown = document.querySelector('.user-dropdown');
        const userAvatar = document.querySelector('.user-avatar');
        const dropdownMenu = document.querySelector('.user-dropdown-menu');
        
        if (!userDropdown || !userAvatar || !dropdownMenu) {
            return; // Elements not found
        }
        
        if (isMobile) {
            // Mobile: Touch-Click Toggle behavior
            let dropdownOpen = false;
            
            userAvatar.addEventListener('click', function(e) {
                if (!dropdownOpen) {
                    // First click: Open dropdown, prevent navigation
                    e.preventDefault();
                    dropdownMenu.classList.add('mobile-show');
                    dropdownOpen = true;
                    
                    // Close dropdown when clicking outside
                    setTimeout(() => {
                        document.addEventListener('click', closeDropdownOutside);
                    }, 10);
                } else {
                    // Second click: Allow navigation to settings
                    // Let the default link behavior happen
                }
            });
            
            function closeDropdownOutside(e) {
                if (!userDropdown.contains(e.target)) {
                    dropdownMenu.classList.remove('mobile-show');
                    dropdownOpen = false;
                    document.removeEventListener('click', closeDropdownOutside);
                }
            }
            
            // Prevent hover behavior on mobile
            userDropdown.classList.add('mobile-device');
            
        } else {
            // Desktop: Keep existing hover behavior
            // No changes needed - CSS hover works fine
        }
    });
})();
