<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['label_id'])) {
    $label_id = $_POST['label_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Verify the label belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM labels WHERE id = ? AND user_id = ?");
        $stmt->execute([$label_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Delete the label (item_labels will be deleted automatically due to ON DELETE CASCADE)
            $stmt = $pdo->prepare("DELETE FROM labels WHERE id = ? AND user_id = ?");
            $stmt->execute([$label_id, $user_id]);
        }

        $pdo->commit();
        header("Location: ../labels.php?notification=" . urlencode("Label deleted successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error deleting label: " . $e->getMessage());
    }
} 