<?php
session_start();
require_once 'config.php'; // Это инициализирует $pdo и $config
global $pdo, $config; // Объявляем, что используем глобальные $pdo и $config

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
    $stmt->execute([$login, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: index.php?error=1");
        exit;
    }
}
?>