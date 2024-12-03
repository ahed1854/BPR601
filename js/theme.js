class ThemeManager {
    constructor() {
        this.html = document.documentElement;
        this.themeToggleBtn = document.querySelector('#themeToggle i');
        this.initialize();
        this.bindEvents();
    }

    initialize() {
        // Check for saved theme or system preference
        const savedTheme = this.getCookie('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'light' || (!savedTheme && !prefersDark)) {
            this.setLightMode(false);
        } else {
            this.setDarkMode(false);
        }
    }

    bindEvents() {
        // Listen for theme toggle button clicks
        const themeToggleBtn = document.querySelector('#themeToggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => this.toggleTheme());
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!this.getCookie('theme')) {
                e.matches ? this.setDarkMode() : this.setLightMode();
            }
        });
    }

    setDarkMode(setCookie = true) {
        this.html.setAttribute('data-theme', 'dark');
        if (this.themeToggleBtn) {
            this.themeToggleBtn.classList.remove('fa-sun');
            this.themeToggleBtn.classList.add('fa-moon');
        }
        
        if (setCookie) {
            this.setCookie('theme', 'dark', 365);
        }
        
        // Force a repaint
        this.forceRepaint();
    }

    setLightMode(setCookie = true) {
        this.html.setAttribute('data-theme', 'light');
        if (this.themeToggleBtn) {
            this.themeToggleBtn.classList.remove('fa-moon');
            this.themeToggleBtn.classList.add('fa-sun');
        }
        
        if (setCookie) {
            this.setCookie('theme', 'light', 365);
        }
        
        // Force a repaint
        this.forceRepaint();
    }

    toggleTheme() {
        const currentTheme = this.html.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            this.setLightMode();
        } else {
            this.setDarkMode();
        }
    }

    forceRepaint() {
        // Force a repaint to ensure styles update
        const htmlStyle = this.html.style.cssText;
        this.html.style.cssText = htmlStyle;
    }

    setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
}

// Initialize theme manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});
