<?php
/**
 * Add expense processing
 * @var PDO $pdo
 * @var Exception $e
 * @var PDOException $pdo_ex
 */
// public/add_expense_action.php
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();

if (!verify_csrf()) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: add_expense.php');
    exit;
}

$userId = $_SESSION['user_id'];

$payment_id = $_POST['payment_id'] ?: null;
$category_id = $_POST['category_id'] ?: null;
$monto = $_POST['monto'] ?? 0;
$motivo = $_POST['motivo'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;

set_old_from_post();

if (!$monto) {
    flash('error', 'El monto es obligatorio.');
    header('Location: add_expense.php');
    exit;
}
if (!validate_decimal($monto, 0.01, 999999.99)) {
    flash('error', 'El monto debe estar entre 0.01 y 999999.99.');
    header('Location: add_expense.php');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO expenses (user_id, payment_id, category_id, monto, descripcion, motivo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $payment_id, $category_id, $monto, $descripcion, $motivo]);
    flash('success', 'Gasto registrado correctamente.');
    $_SESSION['old'] = [];
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al guardar el gasto.');
    header('Location: add_expense.php');
    exit;
}
