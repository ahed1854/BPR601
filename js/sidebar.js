document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.getElementById('overlay');
    
    // Get saved state from localStorage
    const isSidebarOpen = localStorage.getItem('sidebarOpen') === 'true';
    
    // Apply initial state
    if (isSidebarOpen) {
        sidebar.classList.add('open');
        mainContent.classList.add('sidebar-open');
        if (window.innerWidth <= 768) {
            overlay.classList.add('active');
        }
    }
    
    // Toggle sidebar and save state
    menuBtn.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        mainContent.classList.toggle('sidebar-open');
        if (window.innerWidth <= 768) {
            overlay.classList.toggle('active');
        }
        
        // Save state to localStorage
        localStorage.setItem('sidebarOpen', sidebar.classList.contains('open'));
    });
    
    // Close sidebar when clicking overlay (mobile)
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('open');
        mainContent.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        localStorage.setItem('sidebarOpen', false);
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            overlay.classList.remove('active');
        }
    });

    // Handle menu item clicks on mobile
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('open');
                mainContent.classList.remove('sidebar-open');
                overlay.classList.remove('active');
                localStorage.setItem('sidebarOpen', false);
            }
        });
    });
});
