<?php
// Simple benchmark script to measure DB query times for key queries
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';

function timeit($callable, $iterations = 10)
{
    $t0 = microtime(true);
    for ($i = 0; $i < $iterations; $i++) $callable();
    $t1 = microtime(true);
    return ($t1 - $t0) / $iterations;
}

$userId = 1;

$qs = [
    'payments_recent' => function () use ($pdo, $userId) {
        $s = $pdo->prepare("SELECT p.id, p.tipo, p.monto AS pago_monto, p.fecha_pago FROM payments p WHERE p.user_id = ? ORDER BY p.fecha_pago DESC LIMIT 6");
        $s->execute([$userId]);
        $s->fetchAll();
    },
    'payments_total' => function () use ($pdo, $userId) {
        $s = $pdo->prepare("SELECT COALESCE(SUM(monto),0) as t FROM payments WHERE user_id = ?");
        $s->execute([$userId]);
        $s->fetch();
    },
    'expenses_total' => function () use ($pdo, $userId) {
        $s = $pdo->prepare("SELECT COALESCE(SUM(monto),0) as t FROM expenses WHERE user_id = ?");
        $s->execute([$userId]);
        $s->fetch();
    },
];

foreach ($qs as $name => $fn) {
    $avg = timeit($fn, 20);
    echo sprintf("%s: %0.6fs average\n", $name, $avg);
}
