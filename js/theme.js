/**
 * Theme Management System for TimeEffect
 * Handles light/dark/system theme switching with user preferences
 */

class ThemeManager {
    constructor() {
        this.themes = {
            'light': 'Light Mode',
            'dark': 'Dark Mode', 
            'system': 'System Default'
        };
        
        this.currentTheme = this.loadTheme();
        this.init();
    }

    /**
     * Initialize theme system
     */
    init() {
        this.applyTheme(this.currentTheme);
        this.bindEvents();
        
        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (this.currentTheme === 'system') {
                    this.applySystemTheme();
                }
            });
        }
    }

    /**
     * Load theme from localStorage or default to system
     */
    loadTheme() {
        const saved = localStorage.getItem('timeeffect-theme');
        if (saved && this.themes[saved]) {
            return saved;
        }
        return 'system';
    }

    /**
     * Save theme to localStorage and server
     */
    saveTheme(theme) {
        localStorage.setItem('timeeffect-theme', theme);
        
        // Save to server via AJAX
        this.saveToServer(theme);
    }

    /**
     * Apply theme to document
     */
    applyTheme(theme) {
        this.currentTheme = theme;
        
        if (theme === 'system') {
            this.applySystemTheme();
        } else {
            document.documentElement.setAttribute('data-theme', theme);
        }
        
        // Update theme selector if it exists
        this.updateThemeSelector();
    }

    /**
     * Apply system theme based on media query
     */
    applySystemTheme() {
        const isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.removeAttribute('data-theme');
        
        // Add system class for styling purposes
        document.documentElement.classList.toggle('system-dark', isDark);
        document.documentElement.classList.toggle('system-light', !isDark);
    }

    /**
     * Switch to specific theme
     */
    switchTheme(theme) {
        if (!this.themes[theme]) {
            console.warn('Invalid theme:', theme);
            return;
        }
        
        this.applyTheme(theme);
        this.saveTheme(theme);
        
        // Trigger custom event for theme change
        document.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: theme }
        }));
    }

    /**
     * Get current effective theme (resolves 'system' to actual theme)
     */
    getEffectiveTheme() {
        if (this.currentTheme === 'system') {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return this.currentTheme;
    }

    /**
     * Update theme selector UI
     */
    updateThemeSelector() {
        const selector = document.getElementById('theme-selector');
        if (selector) {
            selector.value = this.currentTheme;
        }
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Theme selector dropdown
        const selector = document.getElementById('theme-selector');
        if (selector) {
            selector.addEventListener('change', (e) => {
                this.switchTheme(e.target.value);
            });
        }

        // Theme toggle button (if exists)
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const current = this.getEffectiveTheme();
                const newTheme = current === 'dark' ? 'light' : 'dark';
                this.switchTheme(newTheme);
            });
        }
    }

    /**
     * Save theme preference to server
     */
    async saveToServer(theme) {
        try {
            const response = await fetch('/user/save-theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `theme=${encodeURIComponent(theme)}`
            });
            
            if (!response.ok) {
                console.warn('Failed to save theme to server');
            }
        } catch (error) {
            console.warn('Error saving theme to server:', error);
        }
    }

    /**
     * Create theme selector UI
     */
    createThemeSelector() {
        const selector = document.createElement('select');
        selector.id = 'theme-selector';
        selector.className = 'theme-selector';
        
        Object.entries(this.themes).forEach(([value, label]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            if (value === this.currentTheme) {
                option.selected = true;
            }
            selector.appendChild(option);
        });
        
        return selector;
    }
}

// Initialize theme manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.themeManager = new ThemeManager();
    });
} else {
    window.themeManager = new ThemeManager();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}