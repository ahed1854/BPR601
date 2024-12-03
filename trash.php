<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch trashed notes
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? AND is_trash = 1 ORDER BY updated_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch trashed reminders
$stmt2 = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? AND is_trash = 1 ORDER BY updated_at DESC");
$stmt2->execute([$_SESSION['user_id']]);
$reminders = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// function dd($value)
// {
//     echo "<pre>";
//     var_dump($value);
//     echo "</pre>";
//     die;
// }

// dd($reminders);

?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'dark'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trash - Notes App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/notes.css">
</head>
<body>
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
                <a href="reminders.php" class="menu-item">
                    <i class="fas fa-bell"></i>
                    <span class="menu-text">Reminders</span>
                </a>
                <a href="trash.php" class="menu-item active">
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

            <!-- Empty Trash Button -->
            <button id="emptyTrashBtn" class="w-full note bg-white dark:bg-[#202124] rounded-lg shadow-md p-4 font-bold py-2 px-4 rounded mb-4 border-solid border-2 border-gray-600 hover:border-red-600 dark:hover:bg-red-600 dark:hover:border-red-700 transition-all duration-200">
                Empty Trash
            </button>

            <?php if (empty($notes) && empty($reminders)): ?>
                <div class="text-center text-gray-500 dark:text-gray-400 mt-8">
                    <i class="fas fa-trash text-4xl mb-4"></i>
                    <p>Nothing in trash</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <?php if (!empty($notes)): ?>
                            <?php foreach ($notes as $note): ?>
                                <div class="note bg-white dark:bg-[#202124] rounded-lg shadow-md p-4" data-note-id="<?php echo htmlspecialchars($note['id']); ?>">
                                    <div class="mb-2 font-medium"><?php echo htmlspecialchars($note['title']); ?></div>
                                    <div class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($note['content']); ?></div>
                                    <div class="mt-4 flex justify-end space-x-2">
                                        <button onclick="restoreNote(<?php echo $note['id']; ?>)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button onclick="permanentlyDeleteNote(<?php echo $note['id']; ?>)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full text-red-500">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($reminders)): ?>
                            <?php foreach ($reminders as $reminder): ?>
                                <div class="note bg-white dark:bg-[#202124] rounded-lg shadow-md p-4" data-reminder-id="<?php echo htmlspecialchars($reminder['id']); ?>">
                                    <div class="mb-2 font-medium"><?php echo htmlspecialchars($reminder['title']); ?></div>
                                    <div class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($reminder['content']); ?></div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        <i class="far fa-clock mr-1"></i>
                                        <?php echo date('M j, Y', strtotime($reminder['reminder_date'])); ?>
                                    </div>
                                    <div class="mt-4 flex justify-end space-x-2">
                                        <button onclick="restoreReminder(<?php echo $reminder['id']; ?>)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button onclick="permanentlyDeleteReminder(<?php echo (int)$reminder['id']; ?>)" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full text-red-500">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Empty Trash Confirmation Modal -->
    <div id="emptyTrashModal" class="fixed inset-0 hidden z-[100] min-h-screen bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full mx-4 transform scale-95 opacity-0 transition-all duration-200">
            <h2 class="text-lg font-medium mb-2 text-gray-900 dark:text-gray-100">Empty Trash?</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">All notes and reminders in trash will be permanently deleted. This action cannot be undone.</p>
            <div class="flex justify-end space-x-2">
                <button onclick="closeEmptyTrashModal()" class="px-4 py-2 text-sm rounded bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors duration-200">
                    Cancel
                </button>
                <button onclick="emptyTrash()" class="px-4 py-2 text-sm rounded bg-red-500 dark:bg-red-600 hover:bg-red-600 dark:hover:bg-red-700 text-white transition-colors duration-200">
                    Empty Trash
                </button>
            </div>
        </div>
    </div>

    <script src="js/theme.js"></script>
    <script src="js/sidebar.js"></script>
    <script src="js/trash.js"></script>
</body>
</html>
