<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'dark-bg': '#1a1a1a',
                        'dark-secondary': '#2d2d2d'
                    }
                }
            },
            plugins: [
                function({ addUtilities }) {
                    addUtilities({
                        '.line-clamp-2': {
                            display: '-webkit-box',
                            '-webkit-line-clamp': '2',
                            '-webkit-box-orient': 'vertical',
                            overflow: 'hidden'
                        },
                        '.line-clamp-4': {
                            display: '-webkit-box',
                            '-webkit-line-clamp': '4',
                            '-webkit-box-orient': 'vertical',
                            overflow: 'hidden'
                        },
                        '.line-clamp-6': {
                            display: '-webkit-box',
                            '-webkit-line-clamp': '6',
                            '-webkit-box-orient': 'vertical',
                            overflow: 'hidden'
                        }
                    })
                }
            ]
        }
    </script>
    <style>
        .sidebar {
            transition: width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 4rem;
        }
        .sidebar.collapsed .nav-text {
            display: none;
        }
        .main-content {
            transition: margin-left 0.3s ease;
        }
        .main-content.expanded {
            margin-left: 4rem;
        }
        @media (max-width: 768px) {
            .sidebar:not(.expanded) {
                width: 4rem;
            }
            .sidebar:not(.expanded) .nav-text {
                display: none;
            }
            .main-content {
                margin-left: 4rem;
            }
            .sidebar.expanded {
                width: 16rem;
            }
            .sidebar.expanded .nav-text {
                display: inline;
            }
        }
        .note-content {
            max-height: 200px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        
        .note-content::-webkit-scrollbar {
            width: 6px;
        }
        
        .note-content::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .note-content::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        
        .note-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.7);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }

        body.modal-open {
            overflow: hidden;
        }

        .toast {
            position: fixed;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%) translateY(150%);
            opacity: 0;
            transition: all 0.5s ease;
            z-index: 1000;
            width: 90%;
            max-width: 600px;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-dark-bg transition-colors duration-200" id="body">
    <div id="modal" class="modal items-center justify-center p-4">
        <div class="modal-content bg-white dark:bg-dark-secondary w-full max-w-3xl rounded-xl shadow-2xl p-6">
            <div class="flex justify-between items-start mb-4">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 dark:text-white"></h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modalBody" class="text-gray-700 dark:text-gray-300 whitespace-pre-line"></div>
            <div id="modalFooter" class="mt-6 text-sm text-gray-500 dark:text-gray-400"></div>
        </div>
    </div>
    <div id="confirmModal" class="modal items-center justify-center p-4" style="display: none;">
        <div class="modal-content bg-white dark:bg-dark-secondary w-full max-w-md rounded-xl shadow-2xl p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4" id="confirmTitle"></h2>
            <p class="text-gray-700 dark:text-gray-300 mb-6" id="confirmMessage"></p>
            <div class="flex justify-end space-x-4">
                <button onclick="closeConfirmModal()" 
                    class="px-4 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition duration-300">
                    Cancel
                </button>
                <button id="confirmButton"
                    class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition duration-300">
                    Confirm
                </button>
            </div>
        </div>
    </div>
    <div id="toast" class="toast rounded-lg shadow-lg p-4">
        <div class="flex items-center justify-center">
            <i id="toastIcon" class="mr-4 text-2xl"></i>
            <p id="toastMessage" class="text-base"></p>
        </div>
    </div>
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="sidebar w-64 bg-white dark:bg-dark-secondary shadow-lg transition-colors duration-200 fixed h-full z-50" id="sidebar">
            <div class="h-full flex flex-col">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-8">
                        <button id="toggleSidebar" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white nav-text">Notes App</h1>
                    </div>
                    <nav class="space-y-2">
                        <a href="index.php" class="flex items-center p-3 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'bg-gradient-to-r from-blue-500/10 to-purple-600/10 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-lightbulb w-6 text-center"></i>
                            <span class="ml-3 nav-text">Notes</span>
                        </a>
                        <a href="reminders.php" class="flex items-center p-3 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'reminders.php' ? 'bg-gradient-to-r from-blue-500/10 to-purple-600/10 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-bell w-6 text-center"></i>
                            <span class="ml-3 nav-text">Reminders</span>
                        </a>
                        <a href="trash.php" class="flex items-center p-3 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'trash.php' ? 'bg-gradient-to-r from-blue-500/10 to-purple-600/10 text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-trash w-6 text-center"></i>
                            <span class="ml-3 nav-text">Trash</span>
                        </a>
                        <a href="labels.php" class="flex items-center p-3 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 <?php echo basename($_SERVER['PHP_SELF']) === 'labels.php' ? 'bg-gradient-to-r from-blue-500/10 to-purple-600/10 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300'; ?>">
                            <i class="fas fa-tags w-6 text-center"></i>
                            <span class="ml-3 nav-text">Labels</span>
                        </a>
                    </nav>
                </div>
                <div class="mt-auto p-4 border-t dark:border-gray-700">
                    <button id="themeToggle" class="flex items-center w-full p-3 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-moon w-6 text-center"></i>
                        <span class="ml-3 nav-text">Dark Mode</span>
                    </button>
                    <a href="javascript:void(0)" 
                        onclick="showConfirmModal(
                            'Logout',
                            'Are you sure you want to logout?',
                            () => window.location.href = 'auth/logout.php',
                            'Logout'
                        )"
                        class="flex items-center w-full p-3 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 mt-2">
                        <i class="fas fa-sign-out-alt w-6 text-center"></i>
                        <span class="ml-3 nav-text">Logout</span>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content flex-1 ml-64 p-8 min-h-screen dark:text-white" id="mainContent"> 