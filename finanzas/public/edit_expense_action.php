<?php
/**
 * Edit expense processing
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
    header('Location: expenses.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$id) {
    flash('error', 'Gasto no válido.');
    header('Location: expenses.php');
    exit;
}

$payment_id = $_POST['payment_id'] ?: null;
$category_id = $_POST['category_id'] ?: null;
$monto = $_POST['monto'] ?? 0;
$motivo = $_POST['motivo'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;

set_old_from_post();

if (!$monto) {
    flash('error', 'El monto es obligatorio.');
    header('Location: edit_expense.php?id=' . $id);
    exit;
}
if (!validate_decimal($monto, 0.01, 999999.99)) {
    flash('error', 'El monto debe estar entre 0.01 y 999999.99.');
    header('Location: edit_expense.php?id=' . $id);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE expenses SET payment_id = ?, category_id = ?, monto = ?, descripcion = ?, motivo = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$payment_id, $category_id, $monto, $descripcion, $motivo, $id, $userId]);
    flash('success', 'Gasto actualizado correctamente.');
    $_SESSION['old'] = [];
    header('Location: expenses.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al actualizar el gasto.');
    header('Location: edit_expense.php?id=' . $id);
    exit;
}
?>
