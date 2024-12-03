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
    $id = $data['id'] ?? null;
    $type = $data['type'] ?? 'note';

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID is required']);
        exit();
    }

    $table = ($type === 'reminder') ? 'reminders' : 'notes';

    try {
        $stmt = $pdo->prepare("UPDATE $table SET is_trash = 1 WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$id, $user_id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => ucfirst($type) . ' moved to trash']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move ' . $type . ' to trash']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

