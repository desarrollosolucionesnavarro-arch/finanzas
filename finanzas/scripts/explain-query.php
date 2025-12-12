<?php
// Simple tool to run EXPLAIN on a query and dump the result for diagnostics
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';

if ($argc < 2) {
    echo "Usage: php explain-query.php 'SELECT ...'\n";
    exit(1);
}

$query = $argv[1];
try {
    $stmt = $pdo->query('EXPLAIN ' . $query);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) {
        print_r($r);
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
