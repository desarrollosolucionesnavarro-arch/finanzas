<?php
/**
 * Add expense form
 * @var PDO $pdo
 */
// public/add_expense.php
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();
include __DIR__ . '/../views/header.php';

$error = flash('error');
$success = flash('success');

$payments = $pdo->prepare("SELECT id, tipo, monto, fecha_pago FROM payments WHERE user_id = ? ORDER BY created_at DESC");
$payments->execute([$_SESSION['user_id']]);
$payments = $payments->fetchAll();

$categories = $pdo->query("SELECT id, nombre FROM categories")->fetchAll();

$old_payment = old('payment_id', '');
$old_monto = old('monto', '');
$old_category = old('category_id', '');
$old_motivo = old('motivo', '');
$old_desc = old('descripcion', '');
?>
<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    <h3>Registrar gasto</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post" action="add_expense_action.php">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="mb-3">
            <label class="form-label">Vincular a pago (opcional)</label>
            <select name="payment_id" class="form-select">
                <option value="">Sin vincular a pago</option>
                <?php foreach ($payments as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $old_payment == $p['id'] ? 'selected' : '' ?>>
                        #<?= $p['id'] ?> <?= e($p['tipo']) ?> <?= e($p['fecha_pago']) ?> - $<?= number_format($p['monto'], 2) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" step="0.01" name="monto" class="form-control" required value="<?= e($old_monto) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="category_id" class="form-select">
                <option value="">Sin categoría</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $old_category == $c['id'] ? 'selected' : '' ?>><?= e($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Motivo</label>
            <input type="text" name="motivo" class="form-control" value="<?= e($old_motivo) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control"><?= e($old_desc) ?></textarea>
        </div>

        <button class="btn btn-primary" type="submit">Guardar gasto</button>
    </form>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>