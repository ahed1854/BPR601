<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $item_type = $_POST['item_type']; // 'note' or 'reminder'
    $label_ids = isset($_POST['label_ids']) ? $_POST['label_ids'] : [];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Verify item belongs to user
        $table = $item_type . 's'; // notes or reminders
        $stmt = $pdo->prepare("SELECT id FROM $table WHERE id = ? AND user_id = ?");
        $stmt->execute([$item_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Remove existing labels
            $stmt = $pdo->prepare("DELETE FROM item_labels WHERE item_id = ? AND item_type = ?");
            $stmt->execute([$item_id, $item_type]);
            
            // Add new labels
            if (!empty($label_ids)) {
                $stmt = $pdo->prepare("INSERT INTO item_labels (label_id, item_id, item_type) VALUES (?, ?, ?)");
                foreach ($label_ids as $label_id) {
                    $stmt->execute([$label_id, $item_id, $item_type]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../{$item_type}s.php?notification=" . urlencode("Labels updated successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error updating labels: " . $e->getMessage());
    }
} 