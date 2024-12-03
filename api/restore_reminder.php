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
    $reminder_id = $data['reminder_id'] ?? null;

    if (!$reminder_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reminder ID is required']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM reminders WHERE id = ? AND user_id = ? AND is_trash = 1");
    $stmt->execute([$reminder_id, $user_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reminder not found']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE reminders SET is_trash = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$reminder_id, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Reminder restored successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to restore reminder']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
