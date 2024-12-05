<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $reminder_date = $_POST['reminder_date'];
    $user_id = $_SESSION['user_id'];
    $label_ids = isset($_POST['label_ids']) ? $_POST['label_ids'] : [];

    try {
        $pdo->beginTransaction();

        // Create the reminder
        $stmt = $pdo->prepare("INSERT INTO reminders (title, description, reminder_date, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $reminder_date, $user_id]);
        $reminder_id = $pdo->lastInsertId();

        // Add labels
        if (!empty($label_ids)) {
            $stmt = $pdo->prepare("INSERT INTO item_labels (label_id, item_id, item_type) VALUES (?, ?, 'reminder')");
            foreach ($label_ids as $label_id) {
                $stmt->execute([$label_id, $reminder_id]);
            }
        }

        $pdo->commit();
        header("Location: ../reminders.php?notification=" . urlencode("Reminder created successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error creating reminder: " . $e->getMessage());
    }
} 