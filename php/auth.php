<?php
require_once 'config.php';
session_start();

$action = $_POST['action'] ?? '';

if($action == 'register'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if($password != $confirm_password){
        header('Location: ../register.html?error=mismatch');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);

    if($stmt->fetch()){
        header('Location: ../register.html?error=email_taken');
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hashed]);

    header('Location: ../login.html?success=registered');
    exit;
}
elseif($action == 'login'){
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        header('Location: ../login.html?error=invalid');
        exit;
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];

    header('Location: ../index.php');
    exit;
}
elseif($action == 'logout'){
    session_destroy();
    header('Location: ../login.html');
    exit;
}