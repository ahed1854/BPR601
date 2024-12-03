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
$data = json_decode(file_get_contents('php://input'), true);
$note_id = $data['id'] ?? null;
$title = $data['title'] ?? '';
$content = $data['content'] ?? '';

if (!$note_id || !$content) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Note ID and content are required']);
    exit();
}

try {
    $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$title, $content, $note_id, $user_id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Note updated successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Note not found or update failed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    }

?>

