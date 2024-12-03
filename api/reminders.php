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
        // Get all non-trashed reminders for the user
        try {
            $stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? AND (is_trash = 0 OR is_trash IS NULL) ORDER BY reminder_date ASC");
            $stmt->execute([$user_id]);
            $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'reminders' => $reminders]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'POST':
        // Create new reminder
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $reminderDate = $data['reminderDate'] ?? '';

        if (empty($content) || empty($reminderDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Content and reminder date are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO reminders (user_id, title, content, reminder_date, is_trash) VALUES (?, ?, ?, ?, 0)");
            if ($stmt->execute([$user_id, $title, $content, $reminderDate])) {
                $reminderId = $pdo->lastInsertId();
                
                // Get the created reminder
                $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ?");
                $stmt->execute([$reminderId]);
                $reminder = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'message' => 'Reminder created successfully', 'reminder' => $reminder]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create reminder']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Update reminder
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $title = trim($data['title'] ?? '');
        $content = trim($data['content'] ?? '');
        $reminderDate = $data['reminderDate'] ?? '';

        if (!$id || empty($content) || empty($reminderDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID, content and reminder date are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE reminders SET title = ?, content = ?, reminder_date = ? WHERE id = ? AND user_id = ? AND (is_trash = 0 OR is_trash IS NULL)");
            if ($stmt->execute([$title, $content, $reminderDate, $id, $user_id])) {
                // Get the updated reminder
                $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ?");
                $stmt->execute([$id]);
                $reminder = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode(['success' => true, 'message' => 'Reminder updated successfully', 'reminder' => $reminder]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update reminder']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
?>

