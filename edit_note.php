<?php 
require_once 'includes/header.php';
require_once 'config/database.php';
require_once 'includes/label_selector.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? AND is_deleted = FALSE");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$note = $stmt->fetch();

if (!$note) {
    header("Location: index.php");
    exit();
}
?>

<div class="max-w-4xl mx-auto">
    <form action="actions/update_note.php" method="POST" class="bg-white dark:bg-dark-secondary rounded-xl shadow-lg p-6">
        <input type="hidden" name="note_id" value="<?php echo $note['id']; ?>">
        
        <div class="mb-4">
            <input type="text" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" 
                class="w-full text-xl font-semibold bg-transparent focus:outline-none dark:text-white"
                placeholder="Title">
        </div>

        <div class="mb-6">
            <textarea name="content" rows="8" 
                class="w-full resize-none bg-transparent focus:outline-none dark:text-gray-300"
                placeholder="Note content..."><?php echo htmlspecialchars($note['content']); ?></textarea>
        </div>

        <?php renderLabelSelector($pdo, $_SESSION['user_id'], isset($note) ? $note['id'] : null, 'note'); ?>

        <div class="flex justify-between">
            <a href="index.php" 
                class="px-6 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition duration-300">
                Cancel
            </a>
            <button type="submit" 
                class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-2 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                Update Note
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?> 