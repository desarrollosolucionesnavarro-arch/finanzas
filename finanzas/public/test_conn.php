<?php
/**
 * Database connection test
 * @var PDOException $e
 */
$config = require __DIR__ . '/../app/config.php';
header('Content-Type: text/plain; charset=utf-8');
echo "Usando configuración:\n";
foreach ($config as $k => $v) {
    if (in_array($k, ['db_pass'])) {
        $v = $v === '' ? '(vacío)' : '(oculto)';
    }
    echo " - $k: $v\n";
}

function tryPdo($host)
{
    global $config;
    $dsn = "mysql:host={$host};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
    echo "\nIntentando DSN: $dsn\n";
    try {
        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [PDO::ATTR_TIMEOUT => 5]);
        echo "Conexión PDO OK\n";
        return true;
    } catch (PDOException $e) {
        echo "PDO Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Intento 1: host configurado
tryPdo($config['db_host']);

// Intento 2: 127.0.0.1 como fallback
if ($config['db_host'] !== '127.0.0.1') {
    tryPdo('127.0.0.1');
}

// Intento con mysqli para ver otro error
echo "\nIntento con mysqli:\n";
$m = @mysqli_init();
if (@mysqli_real_connect($m, $config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name'], $config['db_port'])) {
    echo "Conexión mysqli OK\n";
    mysqli_close($m);
} else {
    echo "mysqli Error: " . mysqli_connect_error() . "\n";
}

echo "\nFin de prueba.\n";
