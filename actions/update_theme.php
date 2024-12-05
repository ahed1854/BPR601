<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $theme = $_POST['theme'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE users SET theme = ? WHERE id = ?");
    $stmt->execute([$theme, $user_id]);
    
    $_SESSION['theme'] = $theme;
} 