<?php

/**
 * Edit payment processing
 * @var PDO $pdo
 * @var Exception $e
 * @var PDOException $pdo_ex
 */
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();

if (!verify_csrf()) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$id) {
    flash('error', 'Pago no válido.');
    header('Location: index.php');
    exit;
}

$tipo = $_POST['tipo'] ?? '';
$monto = $_POST['monto'] ?? 0;
$fecha_pago = $_POST['fecha_pago'] ?? null;
$nota = $_POST['nota'] ?? null;

set_old_from_post();

if (!$tipo || !$monto || !$fecha_pago) {
    flash('error', 'Completa los campos obligatorios.');
    header('Location: edit_payment.php?id=' . $id);
    exit;
}
if (!validate_decimal($monto, 0.01, 999999.99)) {
    flash('error', 'El monto debe estar entre 0.01 y 999999.99.');
    header('Location: edit_payment.php?id=' . $id);
    exit;
}
if (!validate_date($fecha_pago)) {
    flash('error', 'La fecha no es válida. Usa formato YYYY-MM-DD.');
    header('Location: edit_payment.php?id=' . $id);
    exit;
}
if (!in_array($tipo, ['quincenal', 'mensual'])) {
    flash('error', 'Tipo de pago no válido.');
    header('Location: edit_payment.php?id=' . $id);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE payments SET tipo = ?, monto = ?, fecha_pago = ?, nota = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$tipo, $monto, $fecha_pago, $nota, $id, $userId]);
    flash('success', 'Pago actualizado correctamente.');
    $_SESSION['old'] = [];
    // limpiar caches
    cache_delete('payments_user_' . $userId);
    cache_delete('totals_user_' . $userId);
    cache_delete('recent_expenses_user_' . $userId);
    header('Location: payment_detail.php?id=' . $id);
    exit;
} catch (Exception $e) {
    flash('error', 'Error al actualizar el pago.');
    header('Location: edit_payment.php?id=' . $id);
    exit;
}
