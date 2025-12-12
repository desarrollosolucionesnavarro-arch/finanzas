<?php
/**
 * User login processing
 * @var PDO $pdo
 * @var Exception $e
 * @var PDOException $pdo_ex
 */
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

/** @var Exception $e */

if (!verify_csrf()) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    flash('error', 'Completa los campos.');
    header('Location: login.php');
    exit;
}
if (!validate_email($email)) {
    flash('error', 'El correo no es válido.');
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        flash('success', 'Sesión iniciada.');
        header('Location: index.php');
        exit;
    } else {
        flash('error', 'Credenciales incorrectas.');
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    flash('error', 'Error al iniciar sesión.');
    header('Location: login.php');
    exit;
}
