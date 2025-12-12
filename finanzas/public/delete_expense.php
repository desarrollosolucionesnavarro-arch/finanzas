<?php
/**
 * Delete expense confirmation
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

// Verificar que el gasto existe y pertenece al usuario
$stmt = $pdo->prepare('SELECT id, monto, motivo, descripcion FROM expenses WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$id, $userId]);
$expense = $stmt->fetch();

if (!$expense) {
    flash('error', 'Gasto no encontrado.');
    header('Location: expenses.php');
    exit;
}
?>

<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    
    <div class="card card-body bg-light">
        <h4 class="card-title">Confirmar eliminación</h4>
        <p class="card-text">¿Estás seguro de que deseas eliminar este gasto?</p>
        
        <div class="alert alert-warning">
            <strong><?= e($expense['motivo']) ?></strong><br>
            Monto: $<?= number_format($expense['monto'], 2) ?><br>
            <?php if ($expense['descripcion']): ?>
                Descripción: <?= e($expense['descripcion']) ?>
            <?php endif; ?>
        </div>

        <form method="post" action="delete_expense_action.php">
            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="id" value="<?= $id ?>">
            
            <button type="submit" class="btn btn-danger">Eliminar</button>
            <a href="javascript:history.back()" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../views/footer.php'; ?>
