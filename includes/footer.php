        </main>
    </div>

    <script>
        // Theme toggling functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const themeIcon = themeToggle.querySelector('i');
        const themeText = themeToggle.querySelector('span');

        function setTheme(isDark) {
            if (isDark) {
                html.classList.add('dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeText.textContent = 'Light Mode';
            } else {
                html.classList.remove('dark');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                themeText.textContent = 'Dark Mode';
            }
        }

        // Check for saved theme preference or system preference
        const darkMode = localStorage.getItem('darkMode') === 'true' || 
                        (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        setTheme(darkMode);

        themeToggle.addEventListener('click', () => {
            const isDark = html.classList.contains('dark');
            setTheme(!isDark);
            localStorage.setItem('darkMode', (!isDark).toString());

            // Update user preference in database using fetch
            fetch('actions/update_theme.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `theme=${!isDark ? 'dark' : 'light'}`
            });
        });

        // Sidebar toggle functionality
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        // Function to handle sidebar state
        function setSidebarState(collapsed) {
            if (collapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('expanded');
            }
        }

        // Check for mobile view
        function checkMobileView() {
            if (window.innerWidth <= 768) {
                setSidebarState(true);
            } else {
                // On desktop, respect user's saved preference
                const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                setSidebarState(isSidebarCollapsed);
            }
        }

        // Initial check
        checkMobileView();

        // Listen for window resize
        window.addEventListener('resize', checkMobileView);

        // Toggle button click handler
        toggleSidebar.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                // On mobile, toggle between expanded and not
                sidebar.classList.toggle('expanded');
            } else {
                // On desktop, toggle between collapsed and not
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            }
        });

        // Modal functionality
        const modal = document.getElementById('modal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modalFooter = document.getElementById('modalFooter');
        const body = document.body;

        function openModal(title, content, footer = '') {
            modalTitle.textContent = title;
            modalBody.textContent = content;
            modalFooter.innerHTML = footer;
            modal.classList.add('show');
            body.classList.add('modal-open');
        }

        function closeModal() {
            modal.classList.remove('show');
            body.classList.remove('modal-open');
        }

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                closeModal();
            }
        });

        // Toast notification functionality
        const toast = document.getElementById('toast');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');
        let toastTimeout;

        function showToast(message, type = 'success') {
            // Clear any existing timeout
            if (toastTimeout) {
                clearTimeout(toastTimeout);
                toast.classList.remove('show');
            }

            // Set icon and colors based on type
            let iconClass, bgClass, textClass;
            switch (type) {
                case 'success':
                    iconClass = 'fas fa-check-circle';
                    bgClass = 'bg-green-500';
                    textClass = 'text-white';
                    break;
                case 'error':
                    iconClass = 'fas fa-exclamation-circle';
                    bgClass = 'bg-red-500';
                    textClass = 'text-white';
                    break;
                case 'info':
                    iconClass = 'fas fa-info-circle';
                    bgClass = 'bg-blue-500';
                    textClass = 'text-white';
                    break;
            }

            // Update toast content and styling
            toast.className = `toast rounded-lg shadow-lg p-4 max-w-md ${bgClass} ${textClass}`;
            toastIcon.className = `mr-3 text-xl ${iconClass}`;
            toastMessage.textContent = message;

            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);

            // Hide toast after 3 seconds
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Check for notification in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('notification')) {
            showToast(decodeURIComponent(urlParams.get('notification')), urlParams.get('type') || 'success');
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Confirmation modal functionality
        const confirmModal = document.getElementById('confirmModal');
        const confirmTitle = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmButton = document.getElementById('confirmButton');
        let confirmCallback = null;

        function showConfirmModal(title, message, callback, confirmText = 'Confirm') {
            confirmTitle.textContent = title;
            confirmMessage.textContent = message;
            confirmButton.textContent = confirmText;
            confirmCallback = callback;
            confirmModal.style.display = 'flex';
            body.classList.add('modal-open');
        }

        function closeConfirmModal() {
            confirmModal.style.display = 'none';
            body.classList.remove('modal-open');
            confirmCallback = null;
        }

        confirmButton.addEventListener('click', () => {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        });

        // Close modal when clicking outside
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                closeConfirmModal();
            }
        });

        // Close with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && confirmModal.style.display === 'flex') {
                closeConfirmModal();
            }
        });
    </script>
</body>
</html> 