<?php

/**
 * Payment detail view
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

$stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$id, $userId]);
$payment = $stmt->fetch();
if (!$payment) {
    flash('error', 'Pago no encontrado.');
    header('Location: index.php');
    exit;
}

$exp = $pdo->prepare('SELECT * FROM expenses WHERE payment_id = ? ORDER BY created_at DESC');
$exp->execute([$id]);
$expenses = $exp->fetchAll();
?>
<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    <h3>Pago #<?= $payment['id'] ?> - <?= e($payment['tipo']) ?></h3>
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <p>Monto: <strong>$<?= number_format($payment['monto'], 2) ?></strong></p>
                    <p>Fecha de pago: <?= e($payment['fecha_pago']) ?></p>
                    <p>Nota: <?= e($payment['nota']) ?></p>
                </div>
            </div>
            <div class="btn-group w-100 mb-3" role="group">
                <a href="edit_payment.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                <a href="delete_payment.php?id=<?= $payment['id'] ?>" class="btn btn-sm btn-danger">🗑️ Eliminar</a>
                <a href="add_expense.php" class="btn btn-sm btn-secondary">➕ Registrar gasto</a>
            </div>
        </div>
        <div class="col-md-6">
            <h5>Gastos vinculados</h5>
            <?php if (empty($expenses)): ?>
                <p>No hay gastos vinculados a este pago.</p>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($expenses as $ex): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <div><strong>$<?= number_format($ex['monto'], 2) ?></strong></div>
                                <div class="small text-muted"><?= e($ex['motivo']) ?> - <?= e($ex['descripcion']) ?></div>
                            </div>
                            <span class="text-muted small"><?= e($ex['created_at']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>