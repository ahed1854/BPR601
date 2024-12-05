<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Delete all trashed notes
        $stmt = $pdo->prepare("DELETE FROM notes WHERE user_id = ? AND is_deleted = TRUE");
        $stmt->execute([$user_id]);

        // Delete all trashed reminders
        $stmt = $pdo->prepare("DELETE FROM reminders WHERE user_id = ? AND is_deleted = TRUE");
        $stmt->execute([$user_id]);

        // Commit transaction
        $pdo->commit();

        header("Location: ../trash.php?notification=" . urlencode("Trash emptied successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        die("Error emptying trash: " . $e->getMessage());
    }
} 