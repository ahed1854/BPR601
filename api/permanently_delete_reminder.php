<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Unauthorized');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['reminder_id'])) {
        throw new Exception('Reminder ID is required');
    }

    $user_id = $_SESSION['user_id'];
    $reminder_id = $data['reminder_id'];

    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ? AND is_trash = 1");
    $result = $stmt->execute([$reminder_id, $user_id]);
    
    if (!$result) {
        throw new Exception('Database error');
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Reminder deleted successfully']);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
