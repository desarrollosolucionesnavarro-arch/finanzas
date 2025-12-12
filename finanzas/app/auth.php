<?php
// Simple auth helpers
function isLogged()
{
    return !empty($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLogged()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser($pdo, $email, $password)
{
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}
