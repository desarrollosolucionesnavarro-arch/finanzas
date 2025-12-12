<?php
/**
 * User registration processing
 * @var PDO $pdo
 */
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

/** @var Exception $e */

if (!verify_csrf()) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: register.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

set_old_from_post();

if (!$nombre || !$email || !$password) {
    flash('error', 'Completa los campos obligatorios.');
    header('Location: register.php');
    exit;
}
if (!validate_email($email)) {
    flash('error', 'El correo no es válido.');
    header('Location: register.php');
    exit;
}
if ($password !== $password_confirm) {
    flash('error', 'Las contraseñas no coinciden.');
    header('Location: register.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        flash('error', 'Ya existe una cuenta con ese correo.');
        header('Location: register.php');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (nombre, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$nombre, $email, $hash]);
    $userId = $pdo->lastInsertId();
    $_SESSION['user_id'] = $userId;
    flash('success', 'Cuenta creada. Bienvenido.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al crear la cuenta.');
    header('Location: register.php');
    exit;
}
