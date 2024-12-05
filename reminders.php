<?php 
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/label_functions.php';

// Check for session notification
if (isset($_SESSION['notification'])) {
    echo "<script>
        window.addEventListener('load', () => {
            showToast('" . addslashes($_SESSION['notification']) . "', '" . $_SESSION['notification_type'] . "');
        });
    </script>";
    unset($_SESSION['notification']);
    unset($_SESSION['notification_type']);
}

// Fetch reminders
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? AND is_deleted = FALSE ORDER BY reminder_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$reminders = $stmt->fetchAll();
?>

<div class="max-w-4xl mx-auto">
    <!-- Add Reminder Form -->
    <form action="actions/save_reminder.php" method="POST" class="mb-8">
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6">
            <input type="text" name="title" placeholder="Reminder Title" required
                class="w-full mb-4 text-xl font-semibold bg-transparent focus:outline-none dark:text-white">
            <textarea name="description" placeholder="Description..." rows="2" required
                class="w-full resize-none bg-transparent focus:outline-none dark:text-gray-300 mb-4"></textarea>
            
            <!-- Add Label Selector -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2 dark:text-gray-300">Labels</label>
                <div class="flex flex-wrap gap-2">
                    <?php 
                    $labels = getAllLabels($pdo, $_SESSION['user_id']);
                    foreach ($labels as $label): 
                    ?>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="label_ids[]" value="<?= $label['id'] ?>" class="mr-2">
                            <span class="w-3 h-3 inline-block mr-1 rounded-full" 
                                  style="background-color: <?= htmlspecialchars($label['color']) ?>"></span>
                            <span class="text-sm dark:text-gray-300"><?= htmlspecialchars($label['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <input type="date" name="reminder_date" required
                    class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 dark:text-white dark:[color-scheme:dark]">
                <button type="submit" 
                    class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                    Add Reminder
                </button>
            </div>
        </div>
    </form>

    <!-- Reminders List -->
    <div class="space-y-4">
        <?php foreach ($reminders as $reminder): ?>
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6 transition-transform hover:scale-105 cursor-pointer"
                 onclick='openModal(<?php 
                     echo json_encode(htmlspecialchars($reminder['title'])) . ', ' . 
                          json_encode(htmlspecialchars($reminder['description'])) . ', ' .
                          json_encode('<i class="fas fa-calendar mr-2"></i>' . date('M d, Y', strtotime($reminder['reminder_date'])));
                     ?>)'>
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-xl font-semibold dark:text-white line-clamp-2 flex-1 mr-4">
                        <?php echo htmlspecialchars($reminder['title']); ?>
                    </h3>
                    <div class="space-x-3 flex-shrink-0" onclick="event.stopPropagation()">
                        <a href="edit_reminder.php?id=<?php echo $reminder['id']; ?>" 
                            class="text-blue-500 hover:text-blue-600">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="javascript:void(0)" 
                            onclick="showConfirmModal(
                                'Delete Reminder',
                                'Are you sure you want to move this reminder to trash?',
                                () => window.location.href = 'actions/delete_reminder.php?id=<?php echo $reminder['id']; ?>',
                                'Move to Trash'
                            )"
                            class="text-red-500 hover:text-red-600">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="text-gray-700 dark:text-gray-300 line-clamp-4 whitespace-pre-line">
                        <?php echo htmlspecialchars($reminder['description']); ?>
                    </p>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                    <i class="fas fa-calendar mr-2"></i>
                    <?php echo date('M d, Y', strtotime($reminder['reminder_date'])); ?>
                </div>
                <div class="mt-3">
                    <?php 
                    $reminderLabels = getItemLabels($pdo, $reminder['id'], 'reminder');
                    displayItemLabels($reminderLabels);
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 