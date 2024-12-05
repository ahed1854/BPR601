<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $label_id = $_POST['label_id'];
    $name = trim($_POST['name']);
    $color = $_POST['color'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // First verify the label belongs to the user
        $stmt = $pdo->prepare("SELECT id FROM labels WHERE id = ? AND user_id = ?");
        $stmt->execute([$label_id, $user_id]);
        
        if ($stmt->fetch()) {
            // Check if another label with the same name exists (excluding current label)
            $stmt = $pdo->prepare("SELECT id FROM labels WHERE name = ? AND user_id = ? AND id != ?");
            $stmt->execute([$name, $user_id, $label_id]);
            
            if ($stmt->fetch()) {
                header("Location: ../labels.php?notification=" . urlencode("A label with this name already exists") . "&type=error");
                exit();
            }

            // Update the label
            $stmt = $pdo->prepare("UPDATE labels SET name = ?, color = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$name, $color, $label_id, $user_id]);
        }

        $pdo->commit();
        header("Location: ../labels.php?notification=" . urlencode("Label updated successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error updating label: " . $e->getMessage());
    }
}

header("Location: ../labels.php");
exit(); 