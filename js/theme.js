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
     * Apply system theme based on media query with Chrome Android fallback
     */
    applySystemTheme() {
        let isDark = false;
        
        // Primary detection method
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            isDark = true;
        }
        
        // Chrome Android fallback detection methods
        if (!isDark && this.isChromeAndroid()) {
            isDark = this.detectDarkModeOnChromeAndroid();
        }
        
        document.documentElement.removeAttribute('data-theme');
        
        // Add system class for styling purposes
        document.documentElement.classList.toggle('system-dark', isDark);
        document.documentElement.classList.toggle('system-light', !isDark);
        
        // Force CSS custom properties update for Chrome Android
        if (this.isChromeAndroid()) {
            this.forceChromeAndroidThemeUpdate(isDark);
        }
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
            let isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Chrome Android fallback
            if (!isDark && this.isChromeAndroid()) {
                isDark = this.detectDarkModeOnChromeAndroid();
            }
            
            return isDark ? 'dark' : 'light';
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
        
        // Update theme toggle button icon
        const toggleBtn = document.getElementById('theme-toggle');
        if (toggleBtn) {
            const effectiveTheme = this.getEffectiveTheme();
            toggleBtn.textContent = effectiveTheme === 'dark' ? '☀️' : '🌙';
            toggleBtn.title = effectiveTheme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
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
    
    /**
     * Detect if running on Chrome Android
     */
    isChromeAndroid() {
        const userAgent = navigator.userAgent;
        return /Android/.test(userAgent) && /Chrome/.test(userAgent) && !/Edge/.test(userAgent);
    }
    
    /**
     * Fallback dark mode detection for Chrome Android
     */
    detectDarkModeOnChromeAndroid() {
        // Method 1: Check if CSS media query works in a different way
        try {
            const testElement = document.createElement('div');
            testElement.style.cssText = 'position:absolute;top:-9999px;left:-9999px;width:1px;height:1px;';
            testElement.style.backgroundColor = 'white';
            document.body.appendChild(testElement);
            
            const computedStyle = window.getComputedStyle(testElement);
            const bgColor = computedStyle.backgroundColor;
            document.body.removeChild(testElement);
            
            // If the background is not white, system might be in dark mode
            if (bgColor !== 'rgb(255, 255, 255)' && bgColor !== 'white') {
                return true;
            }
        } catch (e) {
            console.warn('Chrome Android dark mode detection method 1 failed:', e);
        }
        
        // Method 2: Check meta theme-color if available
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            const content = metaThemeColor.getAttribute('content');
            if (content && (content.includes('#1') || content.includes('#2') || content.includes('#3'))) {
                return true;
            }
        }
        
        // Method 3: Time-based heuristic (dark mode more likely at night)
        const hour = new Date().getHours();
        if (hour < 6 || hour > 20) {
            // Store this preference for consistency
            const stored = localStorage.getItem('chrome-android-dark-hint');
            if (stored === 'true') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Force CSS custom properties update for Chrome Android
     */
    forceChromeAndroidThemeUpdate(isDark) {
        // Force repaint by temporarily changing a CSS custom property
        const root = document.documentElement;
        const tempProp = '--chrome-android-fix';
        
        root.style.setProperty(tempProp, isDark ? 'dark' : 'light');
        
        // Force layout recalculation
        root.offsetHeight;
        
        // Remove temporary property
        root.style.removeProperty(tempProp);
        
        // Store the detected state for consistency
        localStorage.setItem('chrome-android-dark-hint', isDark.toString());
        
        // Dispatch a custom event to trigger any additional updates
        document.dispatchEvent(new CustomEvent('chromeAndroidThemeForced', {
            detail: { isDark }
        }));
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