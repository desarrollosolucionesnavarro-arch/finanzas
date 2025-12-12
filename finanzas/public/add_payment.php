<?php
// public/add_payment.php
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();
include __DIR__ . '/../views/header.php';

$error = flash('error');
$success = flash('success');
$old_tipo = old('tipo', 'quincenal');
$old_monto = old('monto', '');
$old_fecha = old('fecha_pago', '');
$old_nota = old('nota', '');
?>
<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    <h3>Registrar pago</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post" action="add_payment_action.php">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
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
        <button class="btn btn-primary" type="submit">Guardar pago</button>
    </form>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>