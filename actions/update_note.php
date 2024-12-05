<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_id = $_POST['note_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $label_ids = isset($_POST['label_ids']) ? $_POST['label_ids'] : [];

    try {
        $pdo->beginTransaction();

        // First verify the note belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$note_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Update the note
            $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $content, $note_id, $user_id]);

            // Remove existing labels
            $stmt = $pdo->prepare("DELETE FROM item_labels WHERE item_id = ? AND item_type = 'note'");
            $stmt->execute([$note_id]);
            
            // Add new labels
            if (!empty($label_ids)) {
                $stmt = $pdo->prepare("INSERT INTO item_labels (label_id, item_id, item_type) VALUES (?, ?, 'note')");
                foreach ($label_ids as $label_id) {
                    $stmt->execute([$label_id, $note_id]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../index.php?notification=" . urlencode("Note updated successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error updating note: " . $e->getMessage());
    }
} 