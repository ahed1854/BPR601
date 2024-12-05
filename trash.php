<?php 
require_once 'includes/header.php';
require_once 'config/database.php';

// Fetch deleted notes
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? AND is_deleted = TRUE ORDER BY updated_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$deleted_notes = $stmt->fetchAll();

// Fetch deleted reminders
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? AND is_deleted = TRUE ORDER BY updated_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$deleted_reminders = $stmt->fetchAll();
?>

<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold dark:text-white">Deleted Notes</h2>
        <?php if (!empty($deleted_notes) || !empty($deleted_reminders)): ?>
            <form action="actions/empty_trash.php" method="POST" onsubmit="event.preventDefault(); showConfirmModal(
                'Empty Trash',
                'Are you sure you want to permanently delete all items in trash? This action cannot be undone.',
                () => this.submit(),
                'Empty Trash'
            )">
                <button type="submit" 
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition duration-300 flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Empty Trash
                </button>
            </form>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <?php foreach ($deleted_notes as $note): ?>
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg overflow-hidden flex flex-col cursor-pointer"
                 onclick='openModal(<?php 
                     echo json_encode(htmlspecialchars($note['title'])) . ', ' . 
                          json_encode(htmlspecialchars($note['content'])) . ', ' .
                          json_encode(date('M d, Y', strtotime($note['created_at'])));
                 ?>)'>
                <div class="p-6 flex flex-col h-full">
                    <h3 class="text-xl font-semibold mb-3 text-gray-800 dark:text-white line-clamp-2">
                        <?php echo htmlspecialchars($note['title']); ?>
                    </h3>
                    <div class="flex-grow overflow-hidden mb-4">
                        <p class="text-gray-700 dark:text-gray-300 line-clamp-6 whitespace-pre-line">
                            <?php echo htmlspecialchars($note['content']); ?>
                        </p>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 mt-auto">
                        <span><?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                        <div class="space-x-3" onclick="event.stopPropagation()">
                            <a href="actions/restore_note.php?id=<?php echo $note['id']; ?>" 
                                class="text-blue-500 hover:text-blue-600">
                                <i class="fas fa-undo"></i>
                            </a>
                            <a href="javascript:void(0)" 
                                onclick="showConfirmModal(
                                    'Delete Note Permanently',
                                    'Are you sure you want to permanently delete this note? This action cannot be undone.',
                                    () => window.location.href = 'actions/permanent_delete_note.php?id=<?php echo $note['id']; ?>',
                                    'Delete Permanently'
                                )"
                                class="text-red-500 hover:text-red-600">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="text-2xl font-bold mb-6 dark:text-white">Deleted Reminders</h2>
    <div class="space-y-4">
        <?php foreach ($deleted_reminders as $reminder): ?>
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6 cursor-pointer"
                 onclick='openModal(<?php 
                     echo json_encode(htmlspecialchars($reminder['title'])) . ', ' . 
                          json_encode(htmlspecialchars($reminder['description'])) . ', ' .
                          json_encode('<i class="fas fa-calendar mr-2"></i>' . date('M d, Y', strtotime($reminder['reminder_date'])));
                 ?>)'>
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-xl font-semibold dark:text-white"><?php echo htmlspecialchars($reminder['title']); ?></h3>
                    <div class="space-x-3" onclick="event.stopPropagation()">
                        <a href="actions/restore_reminder.php?id=<?php echo $reminder['id']; ?>" 
                            class="text-blue-500 hover:text-blue-600">
                            <i class="fas fa-undo"></i>
                        </a>
                        <a href="javascript:void(0)" 
                            onclick="showConfirmModal(
                                'Delete Reminder Permanently',
                                'Are you sure you want to permanently delete this reminder? This action cannot be undone.',
                                () => window.location.href = 'actions/permanent_delete_reminder.php?id=<?php echo $reminder['id']; ?>',
                                'Delete Permanently'
                            )"
                            class="text-red-500 hover:text-red-600">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
                <p class="text-gray-700 dark:text-gray-300 mb-3"><?php echo nl2br(htmlspecialchars($reminder['description'])); ?></p>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                    <i class="fas fa-calendar mr-2"></i>
                    <?php echo date('M d, Y', strtotime($reminder['reminder_date'])); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 