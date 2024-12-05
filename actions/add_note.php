<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    // Check if both title and content are empty
    if (empty($title) && empty($content)) {
        header("Location: ../index.php?notification=" . urlencode("Cannot create empty note") . "&type=error");
        exit();
    }

    // If title is empty but content exists, use first few words as title
    if (empty($title) && !empty($content)) {
        $words = explode(' ', $content);
        $title = implode(' ', array_slice($words, 0, 5)) . '...';
    }

    // If content is empty but title exists, don't allow it
    if (!empty($title) && empty($content)) {
        header("Location: ../index.php?notification=" . urlencode("Note content cannot be empty") . "&type=error");
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $content]);

    header("Location: ../index.php?notification=" . urlencode("Note created successfully") . "&type=success");
    exit();
} 