<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $color = $_POST['color'];
    $user_id = $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        // Check if label with same name exists
        $stmt = $pdo->prepare("SELECT id FROM labels WHERE name = ? AND user_id = ?");
        $stmt->execute([$name, $user_id]);
        
        if ($stmt->fetch()) {
            header("Location: ../labels.php?notification=" . urlencode("A label with this name already exists") . "&type=error");
            exit();
        }

        // Create the label
        $stmt = $pdo->prepare("INSERT INTO labels (name, color, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $color, $user_id]);
        
        $pdo->commit();
        header("Location: ../labels.php?notification=" . urlencode("Label created successfully") . "&type=success");
        exit();
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error creating label: " . $e->getMessage());
    }
}

// If not POST request, redirect back to labels page
header("Location: ../labels.php");
exit(); 