<?php
/**
 * Add payment processing
 * @var PDO $pdo
 * @var Exception $e
 * @var PDOException $pdo_ex
 */
// public/add_payment_action.php
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();

if (!verify_csrf()) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: add_payment.php');
    exit;
}

$userId = $_SESSION['user_id'];

$tipo = $_POST['tipo'] ?? '';
$monto = $_POST['monto'] ?? 0;
$fecha_pago = $_POST['fecha_pago'] ?? null;
$nota = $_POST['nota'] ?? null;

set_old_from_post();

if (!$tipo || !$monto || !$fecha_pago) {
    flash('error', 'Completa los campos obligatorios.');
    header('Location: add_payment.php');
    exit;
}
if (!validate_decimal($monto, 0.01, 999999.99)) {
    flash('error', 'El monto debe estar entre 0.01 y 999999.99.');
    header('Location: add_payment.php');
    exit;
}
if (!validate_date($fecha_pago)) {
    flash('error', 'La fecha no es válida. Usa formato YYYY-MM-DD.');
    header('Location: add_payment.php');
    exit;
}
if (!in_array($tipo, ['quincenal', 'mensual'])) {
    flash('error', 'Tipo de pago no válido.');
    header('Location: add_payment.php');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, tipo, monto, fecha_pago, nota) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $tipo, $monto, $fecha_pago, $nota]);
    flash('success', 'Pago registrado correctamente.');
    // limpiar old
    $_SESSION['old'] = [];
    // limpiar caches
    cache_delete('payments_user_' . $userId);
    cache_delete('totals_user_' . $userId);
    cache_delete('recent_expenses_user_' . $userId);
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al guardar el pago.');
    header('Location: add_payment.php');
    exit;
}
