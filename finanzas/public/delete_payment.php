<?php
/**
 * Delete payment confirmation
 * @var PDO $pdo
 */
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();
include __DIR__ . '/../views/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$id) {
    flash('error', 'Pago no válido.');
    header('Location: index.php');
    exit;
}

// Verificar que el pago existe y pertenece al usuario
$stmt = $pdo->prepare('SELECT id, tipo, monto, fecha_pago FROM payments WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$id, $userId]);
$payment = $stmt->fetch();

if (!$payment) {
    flash('error', 'Pago no encontrado.');
    header('Location: index.php');
    exit;
}
?>

<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    
    <div class="card card-body bg-light">
        <h4 class="card-title">Confirmar eliminación</h4>
        <p class="card-text">¿Estás seguro de que deseas eliminar este pago?</p>
        
        <div class="alert alert-warning">
            <strong><?= ucfirst($payment['tipo']) ?></strong><br>
            Monto: $<?= number_format($payment['monto'], 2) ?><br>
            Fecha: <?= $payment['fecha_pago'] ?>
        </div>

        <form method="post" action="delete_payment_action.php">
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <button type="submit" class="btn btn-danger">Eliminar</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
