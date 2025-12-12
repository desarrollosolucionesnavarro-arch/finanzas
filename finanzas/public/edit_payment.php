<?php
/**
 * Edit payment form
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

$old_tipo = old('tipo', $payment['tipo']);
$old_monto = old('monto', $payment['monto']);
$old_fecha = old('fecha_pago', $payment['fecha_pago']);
$old_nota = old('nota', $payment['nota']);
$error = flash('error');
$success = flash('success');
?>
<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    <h3>Editar pago #<?= $id ?></h3>

    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <form method="post" action="edit_payment_action.php">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                <option value="quincenal" <?= $old_tipo === 'quincenal' ? 'selected' : '' ?>>Quincenal</option>
                <option value="mensual" <?= $old_tipo === 'mensual' ? 'selected' : '' ?>>Mensual</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" required value="<?= e($old_monto) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de pago</label>
            <input type="date" name="fecha_pago" class="form-control" required value="<?= e($old_fecha) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Nota</label>
            <input type="text" name="nota" class="form-control" value="<?= e($old_nota) ?>">
        </div>
        <button class="btn btn-primary" type="submit">Guardar cambios</button>
        <a href="payment_detail.php?id=<?= $id ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
