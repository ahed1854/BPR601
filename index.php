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

// Fetch notes
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? AND is_deleted = FALSE ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();
?>

<div class="max-w-6xl mx-auto">
    <!-- Add Note Form -->
    <form action="actions/save_note.php" method="POST" class="mb-8">
        <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6">
            <input type="text" name="title" placeholder="Title" required
                class="w-full mb-4 text-xl font-semibold bg-transparent focus:outline-none dark:text-white">
            <textarea name="content" placeholder="Take a note..." rows="3" required
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

            <div class="flex justify-end items-center">
                <button type="submit" 
                    class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                    Add Note
                </button>
            </div>
        </div>
    </form>

    <!-- Notes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($notes as $note): ?>
            <div class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg overflow-hidden transition-transform hover:scale-105 flex flex-col cursor-pointer"
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
                        <div class="note-content">
                            <p class="text-gray-700 dark:text-gray-300 line-clamp-6 whitespace-pre-line">
                                <?php echo htmlspecialchars($note['content']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 mt-auto">
                        <span><?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                        <div class="space-x-3" onclick="event.stopPropagation()">
                            <a href="edit_note.php?id=<?php echo $note['id']; ?>" 
                                class="text-blue-500 hover:text-blue-600">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" 
                                onclick="showConfirmModal(
                                    'Delete Note',
                                    'Are you sure you want to move this note to trash?',
                                    () => window.location.href = 'actions/delete_note.php?id=<?php echo $note['id']; ?>',
                                    'Move to Trash'
                                )"
                                class="text-red-500 hover:text-red-600">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?php 
                        $noteLabels = getItemLabels($pdo, $note['id'], 'note');
                        displayItemLabels($noteLabels);
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 