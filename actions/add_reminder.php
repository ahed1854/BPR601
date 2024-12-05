<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Validate empty fields
    if (empty($title) && empty($description)) {
        header("Location: ../reminders.php?notification=" . urlencode("Cannot create empty reminder") . "&type=error");
        exit();
    }

    if (empty($title)) {
        header("Location: ../reminders.php?notification=" . urlencode("Reminder title is required") . "&type=error");
        exit();
    }

    if (empty($description)) {
        header("Location: ../reminders.php?notification=" . urlencode("Reminder description is required") . "&type=error");
        exit();
    }

    $reminder_date = $_POST['reminder_date'] . ' 00:00:00';
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO reminders (user_id, title, description, reminder_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $reminder_date]);

    header("Location: ../reminders.php?notification=" . urlencode("Reminder created successfully") . "&type=success");
    exit();
} 