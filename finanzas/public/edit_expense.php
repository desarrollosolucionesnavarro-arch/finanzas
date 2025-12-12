<?php
/**
 * Edit expense form
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
    flash('error', 'Gasto no válido.');
    header('Location: expenses.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$id, $userId]);
$expense = $stmt->fetch();

if (!$expense) {
    flash('error', 'Gasto no encontrado.');
    header('Location: expenses.php');
    exit;
}

// Obtener pagos y categorías
$payments = $pdo->prepare("SELECT id, tipo, monto, fecha_pago FROM payments WHERE user_id = ? ORDER BY created_at DESC");
$payments->execute([$userId]);
$payments = $payments->fetchAll();

$categories = $pdo->query("SELECT id, nombre FROM categories")->fetchAll();

$old_payment = old('payment_id', $expense['payment_id']);
$old_monto = old('monto', $expense['monto']);
$old_category = old('category_id', $expense['category_id']);
$old_motivo = old('motivo', $expense['motivo']);
$old_desc = old('descripcion', $expense['descripcion']);
$error = flash('error');
$success = flash('success');
?>
<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    <h3>Editar gasto #<?= $id ?></h3>

    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

    <form method="post" action="edit_expense_action.php">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="mb-3">
            <label class="form-label">Vincular a pago</label>
            <select name="payment_id" class="form-select">
                <option value="">Sin vincular</option>
                <?php foreach ($payments as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $old_payment == $p['id'] ? 'selected' : '' ?>>
                        #<?= $p['id'] ?> <?= e($p['tipo']) ?> - $<?= number_format($p['monto'], 2) ?>
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
        <button class="btn btn-primary" type="submit">Guardar cambios</button>
        <a href="expenses.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
