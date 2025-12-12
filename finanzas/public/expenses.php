<?php
/**
 * Expenses list view
 * @var PDO $pdo
 */
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
require_auth();
include __DIR__ . '/../views/header.php';

$userId = $_SESSION['user_id'];

// Pagination: page & per_page for performance when there are many records
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(10, min(100, (int)($_GET['per_page'] ?? 50)));
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) as c FROM expenses WHERE user_id = ?");
$countStmt->execute([$userId]);
$total = (int)$countStmt->fetch()['c'];

$q = $pdo->prepare("SELECT ex.*, p.tipo as pago_tipo FROM expenses ex LEFT JOIN payments p ON p.id = ex.payment_id WHERE ex.user_id = ? ORDER BY ex.created_at DESC LIMIT ? OFFSET ?");
$q->bindValue(1, $userId, PDO::PARAM_INT);
$q->bindValue(2, $perPage, PDO::PARAM_INT);
$q->bindValue(3, $offset, PDO::PARAM_INT);
$q->execute();
$expenses = $q->fetchAll();
?>
<div class="container mt-4">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary mb-3">← Regresar</a>
    <h3>Gastos</h3>
    <div class="card mt-3">
        <div class="card-body">
            <?php if (empty($expenses)): ?>
                <p>No hay gastos registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>ID</th><th>Fecha</th><th>Motivo</th><th>Pago</th><th>Monto</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach ($expenses as $ex): ?>
                            <tr>
                                <td><?= $ex['id'] ?></td>
                                <td><?= e($ex['created_at']) ?></td>
                                <td><?= e($ex['motivo'] ?: $ex['descripcion']) ?></td>
                                <td><?= e($ex['pago_tipo']) ?></td>
                                <td>$<?= number_format($ex['monto'],2) ?></td>
                                <td>
                                    <a href="edit_expense.php?id=<?= $ex['id'] ?>" class="btn btn-xs btn-sm btn-warning">✏️</a>
                                    <a href="delete_expense.php?id=<?= $ex['id'] ?>" class="btn btn-xs btn-sm btn-danger">🗑️</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation" class="mt-3">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>&per_page=<?= $perPage ?>">Anterior</a></li>
                        <?php endif; ?>
                        <?php if (($offset + count($expenses)) < $total): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>&per_page=<?= $perPage ?>">Siguiente</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
