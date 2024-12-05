<?php
require_once 'config/database.php';
require_once 'includes/header.php';
require_once 'includes/label_functions.php';

$user_id = $_SESSION['user_id'];
$labels = getAllLabels($pdo, $user_id);
?>

<div class="max-w-4xl mx-auto">
    <form action="actions/save_note.php" method="POST" class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6">
        <div class="mb-4">
            <input type="text" name="title" 
                class="w-full text-xl font-semibold bg-transparent focus:outline-none dark:text-white"
                placeholder="Title" required>
        </div>

        <div class="mb-6">
            <textarea name="content" rows="8" 
                class="w-full resize-none bg-transparent focus:outline-none dark:text-gray-300"
                placeholder="Note content..." required></textarea>
        </div>

        <!-- Add Label Selector -->
        <div class="mb-6">
            <label class="block text-sm font-medium mb-2 dark:text-gray-300">Labels</label>
            <div class="space-y-2">
                <?php foreach ($labels as $label): ?>
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="label_ids[]" 
                               value="<?= $label['id'] ?>" 
                               class="mr-2">
                        <span class="w-4 h-4 inline-block mr-2 rounded-full" 
                              style="background-color: <?= htmlspecialchars($label['color']) ?>"></span>
                        <span class="dark:text-gray-300"><?= htmlspecialchars($label['name']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-between">
            <a href="index.php" 
                class="px-6 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition duration-300">
                Cancel
            </a>
            <button type="submit" 
                class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                Save Note
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 