<?php

/**
 * Dashboard - Resumen de Finanzas
 * @var PDO $pdo
 */
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';
include __DIR__ . '/../views/header.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

$error = flash('error');
$success = flash('success');

// Pagos recientes (LIMIT 6 para mejor rendimiento)
// Cache recent payments to avoid frequent DB calls
$paymentsCacheKey = 'payments_user_' . $userId;
$payments = cache_get($paymentsCacheKey);
if ($payments === null) {
    $stmt = $pdo->prepare(
        "SELECT p.id, p.tipo, p.monto AS pago_monto, p.fecha_pago
    FROM payments p
    WHERE p.user_id = ?
    ORDER BY p.fecha_pago DESC LIMIT 6"
    );
    $stmt->execute([$userId]);
    $payments = $stmt->fetchAll();
    cache_set($paymentsCacheKey, $payments, 30);
}

// Gastos por pago (query separada)
if (!empty($payments)) {
    $ids = array_column($payments, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $expenseStmt = $pdo->prepare("SELECT payment_id, COALESCE(SUM(monto),0) as total FROM expenses WHERE payment_id IN ($placeholders) GROUP BY payment_id");
    $expenseStmt->execute($ids);
    $expenses = $expenseStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($payments as &$p) {
        $p['total_gastado'] = $expenses[$p['id']] ?? 0;
    }
}

// Totales generales (queries simples)
// Cache totals for a small TTL to reduce SUM computation frequency
$totalsCacheKey = 'totals_user_' . $userId;
$totals = cache_get($totalsCacheKey);
if ($totals === null) {
    $t1 = $pdo->prepare("SELECT COALESCE(SUM(monto),0) as t FROM payments WHERE user_id = ?");
    $t1->execute([$userId]);
    $totIngresos = $t1->fetch()['t'] ?? 0;

    $t2 = $pdo->prepare("SELECT COALESCE(SUM(monto),0) as t FROM expenses WHERE user_id = ?");
    $t2->execute([$userId]);
    $totGastos = $t2->fetch()['t'] ?? 0;
    $totals = ['ingresos' => $totIngresos, 'gastos' => $totGastos];
    cache_set($totalsCacheKey, $totals, 30);
} else {
    $totIngresos = $totals['ingresos'];
    $totGastos = $totals['gastos'];
}

// Gastos recientes
$recent = $pdo->prepare("SELECT ex.id, ex.monto, ex.descripcion, ex.motivo, ex.created_at, p.tipo as pago_tipo
    FROM expenses ex
    LEFT JOIN payments p ON p.id = ex.payment_id
    WHERE ex.user_id = ?
    ORDER BY ex.created_at DESC LIMIT 5");
$recentCacheKey = 'recent_expenses_user_' . $userId;
$recentExpenses = cache_get($recentCacheKey);
if ($recentExpenses === null) {
    $recent->execute([$userId]);
    $recentExpenses = $recent->fetchAll();
    cache_set($recentCacheKey, $recentExpenses, 30);
}
?>
<div class="container-fluid mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-dark">Resumen de Finanzas</h1>
            <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= e($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?= e($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-8">
            <div class="row">
                <?php if (empty($payments)): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center py-5">
                            <h5>📊 Sin pagos registrados</h5>
                            <p class="mb-2">Comienza a registrar tus ingresos para gestionar tus finanzas.</p>
                            <a href="add_payment.php" class="btn btn-info btn-sm">➕ Agregar primer pago</a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php foreach ($payments as $p): ?>
                    <?php $saldo = $p['pago_monto'] - $p['total_gastado']; ?>
                    <div class="col-lg-6 col-md-12 mb-3">
                        <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid #007bff;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="badge bg-info text-dark mb-2"><?= e($p['tipo']) ?></h6>
                                        <p class="card-text text-muted small">📅 <?= e($p['fecha_pago']) ?></p>
                                    </div>
                                </div>
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <small class="text-muted d-block">Ingreso</small>
                                        <strong class="text-success">$<?= number_format($p['pago_monto'], 2) ?></strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Gastado</small>
                                        <strong class="text-danger">$<?= number_format($p['total_gastado'], 2) ?></strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Saldo</small>
                                        <strong class="text-<?= $saldo >= 0 ? 'success' : 'danger' ?>">$<?= number_format($saldo, 2) ?></strong>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $p['pago_monto'] > 0 ? round(($p['total_gastado'] / $p['pago_monto']) * 100) : 0 ?>%"></div>
                                    </div>
                                    <small class="text-muted">Gastado: <?= $p['pago_monto'] > 0 ? round(($p['total_gastado'] / $p['pago_monto']) * 100) : 0 ?>%</small>
                                </div>
                                <a href="payment_detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary mt-3 w-100">Ver detalles →</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0 mt-2">💰 Gastos recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentExpenses)): ?>
                        <p class="text-muted text-center py-4 mb-0">No hay gastos registrados aún.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentExpenses as $r): ?>
                                <div class="list-group-item px-0 py-3">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <strong><?= e($r['motivo'] ?: 'Sin motivo') ?></strong>
                                            <p class="mb-1 small text-muted">📝 <?= e($r['descripcion']) ?> | 📅 <?= e($r['created_at']) ?></p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-danger">-$<?= number_format($r['monto'], 2) ?></span>
                                            <?php if ($r['pago_tipo']): ?><br><small class="text-muted"><?= e($r['pago_tipo']) ?></small><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="expenses.php" class="btn btn-sm btn-outline-secondary mt-3">Ver todos los gastos →</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="card-body">
                    <h6 class="card-title mb-3 opacity-75">📊 RESUMEN GENERAL</h6>
                    <div class="mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.25);">
                        <p class="mb-1 opacity-75 small">Ingresos totales</p>
                        <h4 class="mb-0">$<?= number_format($totIngresos, 2) ?></h4>
                    </div>
                    <div class="mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.25);">
                        <p class="mb-1 opacity-75 small">Gastos totales</p>
                        <h4 class="mb-0">-$<?= number_format($totGastos, 2) ?></h4>
                    </div>
                    <div>
                        <p class="mb-1 opacity-75 small">Saldo disponible</p>
                        <h3 class="mb-0">$<?= number_format($totIngresos - $totGastos, 2) ?></h3>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h5 class="card-title mb-0 mt-2">⚡ Acciones rápidas</h5>
                </div>
                <div class="card-body">
                    <a href="add_payment.php" class="btn btn-primary w-100 mb-2">➕ Nuevo pago</a>
                    <a href="add_expense.php" class="btn btn-warning w-100 mb-2">💸 Registrar gasto</a>
                    <hr>
                    <a href="expenses.php" class="btn btn-outline-secondary w-100 btn-sm">Ver todos los gastos</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>