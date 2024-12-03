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

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get all non-trashed notes for the user
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? AND is_trash = 0 ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        echo json_encode(['success' => true, 'notes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'POST':
        // Create a new note
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content, is_trash) VALUES (?, ?, ?, 0)");
        if ($stmt->execute([$user_id, $title, $content])) {
            $note_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
            $stmt->execute([$note_id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'message' => 'Note created successfully', 'note' => $note]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create note']);
        }
        break;

    case 'PUT':
        // Update a note
        $data = json_decode(file_get_contents('php://input'), true);
        $note_id = $data['id'];
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');

        $stmt = $pdo->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ? AND user_id = ? AND is_trash = 0");
        if ($stmt->execute([$title, $content, $note_id, $user_id])) {
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
            $stmt->execute([$note_id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'message' => 'Note updated successfully', 'note' => $note]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update note']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>

