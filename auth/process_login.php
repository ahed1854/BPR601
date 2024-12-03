<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'All fields are required'
        ];
        header("Location: ../login.php");
        exit();
    }

    // Check user credentials
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // Preserve theme preference
        if (isset($_COOKIE['theme'])) {
            $_SESSION['theme'] = $_COOKIE['theme'];
        }
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Welcome back, ' . htmlspecialchars($user['username']) . '! 👋'
        ];
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Invalid username or password'
        ];
        header("Location: ../login.php");
        exit();
    }
}

header("Location: ../login.php");
exit();
?>
