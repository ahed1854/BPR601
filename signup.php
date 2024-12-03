<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Notes App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'keep-gray': '#202124',
                        'keep-dark': '#202124',
                        'keep-light': '#ffffff',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-keep-dark transition-colors duration-200">
    <div class="absolute top-4 right-4">
        <button id="themeToggle" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
            <i class="fas fa-moon text-gray-600 dark:text-gray-300 dark:hidden"></i>
            <i class="fas fa-sun text-gray-300 hidden dark:block"></i>
        </button>
    </div>

    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md">
            <div class="flex justify-center items-center mb-8">
                <div class="flex items-center space-x-3">
                    <img src="https://www.gstatic.com/images/branding/product/1x/keep_2020q4_48dp.png" alt="Keep" class="h-10 w-10">
                    <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">Keep</span>
                </div>
            </div>
            
            <div class="bg-white dark:bg-[#2d2e30] p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold mb-6 text-center text-gray-800 dark:text-gray-200">Sign Up</h2>
                <form action="auth/process_signup.php" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 dark:text-gray-300 mb-2">Username</label>
                        <input type="text" id="username" name="username" required
                            class="w-full p-2 border dark:border-gray-600 rounded bg-white dark:bg-[#525355] text-gray-800 dark:text-gray-200 focus:outline-none focus:border-blue-500 dark:focus:border-blue-400">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full p-2 border dark:border-gray-600 rounded bg-white dark:bg-[#525355] text-gray-800 dark:text-gray-200 focus:outline-none focus:border-blue-500 dark:focus:border-blue-400">
                    </div>
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full p-2 border dark:border-gray-600 rounded bg-white dark:bg-[#525355] text-gray-800 dark:text-gray-200 focus:outline-none focus:border-blue-500 dark:focus:border-blue-400">
                    </div>
                    <button type="submit"
                        class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition duration-200">
                        Sign Up
                    </button>
                </form>
                <p class="mt-4 text-center text-gray-600 dark:text-gray-400">
                    Already have an account? <a href="login.php" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 dark:hover:text-blue-300">Login</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        // Check for saved theme preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });
    </script>
</body>
</html>
