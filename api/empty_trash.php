<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Delete all trashed notes
        $stmt = $pdo->prepare("DELETE FROM notes WHERE user_id = ? AND is_trash = 1");
        $stmt->execute([$user_id]);

        // Delete all trashed reminders
        $stmt = $pdo->prepare("DELETE FROM reminders WHERE user_id = ? AND is_trash = 1");
        $stmt->execute([$user_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Trash emptied successfully']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to empty trash']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
