import Alpine from 'alpinejs';

Alpine.data('appLayout', () => ({
    sidebarOpen: false,
    darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    
    init() {
        // Ensure any legacy collapsed class and key are removed
        document.documentElement.classList.remove('sidebar-collapsed');
        localStorage.removeItem('sidebarOpen');

        // Theme synchronization
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        }

        window.addEventListener('theme-changed', (e) => {
            if (e.detail && typeof e.detail.darkMode === 'boolean') {
                this.darkMode = e.detail.darkMode;
                document.documentElement.classList.toggle('dark', this.darkMode);
                document.documentElement.style.colorScheme = this.darkMode ? 'dark' : 'light';
            }
        });
    },
    
    toggleTheme() {
        this.darkMode = !this.darkMode;
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
            document.documentElement.style.colorScheme = 'dark';
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
            localStorage.setItem('theme', 'light');
        }
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { darkMode: this.darkMode } }));
    }
}));
