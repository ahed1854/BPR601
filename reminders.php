<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get and clear any notification
$notification = isset($_SESSION['notification']) ? $_SESSION['notification'] : null;
unset($_SESSION['notification']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'dark'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/notes.css">
    <link rel="stylesheet" href="css/reminders.css">
    <style>
        .notification {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            pointer-events: none;
        }
        .notification.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }
        .notification button {
            pointer-events: auto;
        }
    </style>
</head>
<body>
    <!-- Notification -->
    <?php if ($notification): ?>
    <div id="notification" class="notification fixed bottom-4 inset-x-0 mx-auto w-auto max-w-md px-4 py-3 rounded-lg shadow-lg z-50 flex items-center justify-center space-x-2 <?php 
        echo $notification['type'] === 'error' 
            ? 'bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200' 
            : 'bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200';
    ?>">
        <i class="fas <?php echo $notification['type'] === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
        <span class="text-center"><?php echo htmlspecialchars($notification['message']); ?></span>
        <button onclick="closeNotification()" class="ml-4 <?php 
            echo $notification['type'] === 'error'
                ? 'text-red-700 dark:text-red-200 hover:text-red-900 dark:hover:text-red-100'
                : 'text-green-700 dark:text-green-200 hover:text-green-900 dark:hover:text-green-100';
        ?>">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 shadow-md z-50">
        <div class="flex items-center justify-between h-16 px-4">
            <!-- Left side -->
            <div class="flex items-center space-x-4">
                <button id="menuBtn" class="p-2 hover:bg-[#28292C] rounded-full">
                    <i class="fas fa-bars"></i>
                </button>
                <img src="https://www.gstatic.com/images/branding/product/1x/keep_2020q4_48dp.png" alt="Keep" class="h-12">
                <span class="text-xl"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <button id="themeToggle" class="p-2 rounded-full theme-toggle-btn">
                    <i class="fas fa-moon"></i>
                </button>
                <button onclick="showLogoutModal()" class="p-2 rounded-full hover:bg-[#28292C] transition-colors duration-200">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Overlay -->
    <div id="overlay" class="overlay"></div>

    <!-- Sidebar -->
    <aside id="sidebar">
        <nav class="h-full py-2">
            <div class="space-y-1">
                <a href="index.php" class="menu-item">
                    <i class="fas fa-lightbulb"></i>
                    <span class="menu-text">Notes</span>
                </a>
                <a href="reminders.php" class="menu-item active">
                    <i class="fas fa-bell"></i>
                    <span class="menu-text">Reminders</span>
                </a>
                <a href="trash.php" class="menu-item">
                    <i class="fas fa-trash"></i>
                    <span class="menu-text">Trash</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 hidden z-[100] min-h-screen bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full mx-4 transform scale-95 opacity-0 transition-all duration-200">
            <h2 class="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100">Confirm Logout</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Are you sure you want to log out?</p>
            <div class="flex justify-end space-x-2">
                <button onclick="closeLogoutModal()" class="px-4 py-2 text-sm rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <a href="auth/logout.php" class="px-4 py-2 text-sm rounded bg-red-500 dark:bg-red-600 hover:bg-red-600 dark:hover:bg-red-700 text-white transition-colors duration-200">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content min-h-screen">
        <div class="container mx-auto px-4 py-8">
            
            <!-- Note Creation Form -->
            <div class="mb-8 max-w-xl mx-auto">
                <form id="reminderForm" class="rounded-lg p-3">
                    <div class="space-y-1">
                        <input type="text" id="title" placeholder="Title" class="w-full p-2 bg-transparent focus:outline-none text-lg" />
                    </div>
                    <div class="space-y-1">
                        <textarea id="content" placeholder="Add a reminder" class="w-full min-h-[20px] p-2 bg-transparent focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="flex space-x-1">
                        <input type="date" id="reminderDate" name="reminderDate" class="w-full p-2 bg-transparent focus:outline-none text-lg" />
                        <button type="submit" class="px-4 py-1 text-sm rounded">
                            Add
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reminders Grid -->
            <div id="remindersGrid" class="grid grid-cols-1 gap-4">
            </div>
        </div>

    </main>

    <script>
        // Notification functionality
        const notification = document.getElementById('notification');
        if (notification) {
            // Show notification with animation
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            // Auto-hide after 3 seconds
            setTimeout(() => {
                closeNotification();
            }, 3000);
        }

        function closeNotification() {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }
    </script>
    <script>
        // Logout Modal functionality
        function showLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const modalContent = modal.querySelector('div > div');
            modal.classList.remove('hidden');
            // Small delay to ensure the display:flex is applied before the transform
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const modalContent = modal.querySelector('div > div');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Close modal when clicking outside
        document.getElementById('logoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('logoutModal').classList.contains('hidden')) {
                closeLogoutModal();
            }
        });
    </script>
    <script src="js/theme.js"></script>
    <script src="js/reminders.js"></script>
    <script src="js/sidebar.js"></script>
</body>
</html>
