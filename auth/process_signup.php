<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../signup.php");
        exit();
    }

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Username or email already exists";
        header("Location: ../signup.php");
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$username, $email, $hashedPassword]);
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: ../login.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../signup.php");
        exit();
    }
}

header("Location: ../signup.php");
exit();
?>
