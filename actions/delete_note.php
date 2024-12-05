<?php
session_start();
require_once '../config/database.php';

if (isset($_GET['id'])) {
    $note_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE notes SET is_deleted = TRUE WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $user_id]);

    header("Location: ../index.php?notification=" . urlencode("Note moved to trash") . "&type=info");
    exit();
} 