<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $reminder_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // First verify that the reminder belongs to the user and is in trash
        $stmt = $pdo->prepare("SELECT id FROM reminders WHERE id = ? AND user_id = ? AND is_deleted = TRUE");
        $stmt->execute([$reminder_id, $user_id]);
        
        if ($stmt->fetch()) {
            // If verification passes, restore the reminder
            $stmt = $pdo->prepare("UPDATE reminders SET is_deleted = FALSE WHERE id = ? AND user_id = ?");
            $stmt->execute([$reminder_id, $user_id]);
        }

        $pdo->commit();
        header("Location: ../trash.php?notification=" . urlencode("Reminder restored successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error restoring reminder: " . $e->getMessage());
    }
} 