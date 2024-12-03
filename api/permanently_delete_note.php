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
    $data = json_decode(file_get_contents('php://input'), true);
    $note_id = $data['note_id'] ?? null;

    if (!$note_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Note ID is required']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ? AND is_trash = 1");
    if ($stmt->execute([$note_id, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Note permanently deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete note']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>

