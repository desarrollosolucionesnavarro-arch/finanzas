<?php
// Simple script to create indexes for MySQL or SQLite to improve performance.
// Usage: php create-db-indexes.php

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';

try {
    $stmts = [
        "ALTER TABLE payments ADD INDEX idx_payments_user_id (user_id)",
        "ALTER TABLE payments ADD INDEX idx_payments_created_at (created_at)",
        "ALTER TABLE payments ADD INDEX idx_payments_fecha_pago (fecha_pago)",
        "ALTER TABLE expenses ADD INDEX idx_expenses_user_id (user_id)",
        "CREATE INDEX idx_expenses_payment_id ON expenses(payment_id)"
    ];

    foreach ($stmts as $s) {
        try {
            $pdo->exec($s);
            echo "Ejecutado: $s\n";
        } catch (Exception $e) {
            echo "Ignorado (posible que ya exista o no aplicable): " . $e->getMessage() . "\n";
        }
    }
    echo "Finalizado.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
