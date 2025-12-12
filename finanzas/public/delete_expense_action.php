<?php
/**
 * Delete expense processing
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

try {
    $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    flash('success', 'Gasto eliminado correctamente.');
    header('Location: expenses.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al eliminar el gasto.');
    header('Location: expenses.php');
    exit;
}
?>
