<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reminder_id = $_POST['reminder_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $reminder_date = $_POST['reminder_date'];
    $user_id = $_SESSION['user_id'];
    $label_ids = isset($_POST['label_ids']) ? $_POST['label_ids'] : [];

    try {
        $pdo->beginTransaction();

        // First verify the reminder belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM reminders WHERE id = ? AND user_id = ?");
        $stmt->execute([$reminder_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Update the reminder
            $stmt = $pdo->prepare("UPDATE reminders SET title = ?, description = ?, reminder_date = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $reminder_date, $reminder_id, $user_id]);

            // Remove existing labels
            $stmt = $pdo->prepare("DELETE FROM item_labels WHERE item_id = ? AND item_type = 'reminder'");
            $stmt->execute([$reminder_id]);
            
            // Add new labels
            if (!empty($label_ids)) {
                $stmt = $pdo->prepare("INSERT INTO item_labels (label_id, item_id, item_type) VALUES (?, ?, 'reminder')");
                foreach ($label_ids as $label_id) {
                    $stmt->execute([$label_id, $reminder_id]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../reminders.php?notification=" . urlencode("Reminder updated successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error updating reminder: " . $e->getMessage());
    }
} 