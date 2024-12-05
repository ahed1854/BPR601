<?php 
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/label_selector.php';

if (!isset($_GET['id'])) {
    header("Location: reminders.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ? AND is_deleted = FALSE");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$reminder = $stmt->fetch();

if (!$reminder) {
    header("Location: reminders.php");
    exit();
}
?>

<div class="max-w-4xl mx-auto">
    <form action="actions/update_reminder.php" method="POST" class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6">
        <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
        
        <div class="mb-4">
            <input type="text" name="title" value="<?php echo htmlspecialchars($reminder['title']); ?>" 
                class="w-full text-xl font-semibold bg-transparent focus:outline-none dark:text-white"
                placeholder="Reminder Title" required>
        </div>

        <div class="mb-4">
            <textarea name="description" rows="4" required
                class="w-full resize-none bg-transparent focus:outline-none dark:text-gray-300"
                placeholder="Description..."><?php echo htmlspecialchars($reminder['description']); ?></textarea>
        </div>

        <div class="mb-6">
            <input type="date" name="reminder_date" 
                value="<?php echo date('Y-m-d', strtotime($reminder['reminder_date'])); ?>" required
                class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 dark:text-white dark:[color-scheme:dark]">
        </div>

        <?php renderLabelSelector($pdo, $_SESSION['user_id'], isset($reminder) ? $reminder['id'] : null, 'reminder'); ?>

        <div class="flex justify-between">
            <a href="reminders.php" 
                class="px-6 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition duration-300">
                Cancel
            </a>
            <button type="submit" 
                class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                Update Reminder
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 