<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    $label_ids = isset($_POST['label_ids']) ? $_POST['label_ids'] : [];

    try {
        $pdo->beginTransaction();

        // Create the note
        $stmt = $pdo->prepare("INSERT INTO notes (title, content, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $user_id]);
        $note_id = $pdo->lastInsertId();

        // Add labels
        if (!empty($label_ids)) {
            $stmt = $pdo->prepare("INSERT INTO item_labels (label_id, item_id, item_type) VALUES (?, ?, 'note')");
            foreach ($label_ids as $label_id) {
                $stmt->execute([$label_id, $note_id]);
            }
        }

        $pdo->commit();
        header("Location: ../index.php?notification=" . urlencode("Note created successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error creating note: " . $e->getMessage());
    }
} 