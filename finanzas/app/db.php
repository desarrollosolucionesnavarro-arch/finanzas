<?php
/**
 * Database connection file
 * 
 * @global PDO $pdo
 */

$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'UTC');

/** @var PDO $pdo */
$pdo = null;
$errors = [];

$port = isset($config['db_port']) ? (int)$config['db_port'] : null;

$tryHosts = [];
if (!empty($config['db_host'])) $tryHosts[] = $config['db_host'];
$tryHosts[] = '127.0.0.1';
$tryHosts[] = '::1';
$tryHosts[] = 'localhost';

foreach (array_unique($tryHosts) as $host) {
    try {
        if (!empty($config['db_socket'])) {
            $dsn = "mysql:unix_socket={$config['db_socket']};dbname={$config['db_name']};charset=utf8mb4";
        } else {
            $dsn = 'mysql:host=' . $host;
            if ($port) $dsn .= ';port=' . $port;
            $dsn .= ';dbname=' . $config['db_name'] . ';charset=utf8mb4';
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Keep connect timeout short to avoid long waits when DB is down
            PDO::ATTR_TIMEOUT => 2,
            // PDO persistent connection is optionally enabled via config
            PDO::ATTR_PERSISTENT => (!empty($config['db_persistent']) && $config['db_persistent'] === true),
        ];

        $pdo = new PDO($dsn, $config['db_user'] ?? null, $config['db_pass'] ?? null, $options);
        // Intentar crear índices útiles para rendimiento (silenciar errores si ya existen o no se puede)
        try {
            $pdo->exec("ALTER TABLE payments ADD INDEX idx_payments_user_id (user_id);");
        } catch (Exception $e) { /* ignore if exists */ }
        try {
            $pdo->exec("ALTER TABLE payments ADD INDEX idx_payments_fecha_pago (fecha_pago);");
        } catch (Exception $e) { /* ignore if exists */ }
        try {
            $pdo->exec("ALTER TABLE payments ADD INDEX idx_payments_created_at (created_at);");
        } catch (Exception $e) { /* ignore if exists */ }
        try {
            $pdo->exec("ALTER TABLE expenses ADD INDEX idx_expenses_user_id (user_id);");
        } catch (Exception $e) { /* ignore if exists */ }
        try {
            $pdo->exec("CREATE INDEX idx_expenses_payment_id ON expenses(payment_id);");
        } catch (Exception $e) { /* ignore if exists */ }
        break;
    } catch (PDOException $e) {
        $errors[] = "host={$host} port=" . ($port ?? '(undef)') . " -> " . $e->getMessage();
    }
}

if ($pdo === null) {
    // Intentar usar SQLite como fallback para desarrollo local
    $sqlitePath = __DIR__ . '/../data/finanzas.sqlite';
    if (!is_dir(__DIR__ . '/../data')) {
        @mkdir(__DIR__ . '/../data', 0755, true);
    }
    try {
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Ejecutar migraciones básicas si las tablas no existen
        $pdo->exec("PRAGMA foreign_keys = ON;");

        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            email TEXT UNIQUE,
            password_hash TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            tipo TEXT NOT NULL,
            monto NUMERIC NOT NULL,
            fecha_pago DATE NOT NULL,
            nota TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
        );");

        $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            payment_id INTEGER,
            category_id INTEGER,
            monto NUMERIC NOT NULL,
            descripcion TEXT,
            motivo TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY(payment_id) REFERENCES payments(id) ON DELETE SET NULL,
            FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE SET NULL
        );");

        // Crear índices necesarios para mejorar rendimiento en consultas por usuario y pagos
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_payments_created_at ON payments(created_at);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_expenses_user_id ON expenses(user_id);");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_expenses_payment_id ON expenses(payment_id);");

        // Insertar categorías por defecto si están vacías
        $count = $pdo->query("SELECT COUNT(*) as c FROM categories")->fetch(PDO::FETCH_ASSOC);
        if (!$count || $count['c'] == 0) {
            $stmt = $pdo->prepare("INSERT INTO categories (nombre) VALUES (?)");
            $defaults = ['Alimentos','Transporte','Servicios','Entretenimiento','Otros'];
            foreach ($defaults as $d) $stmt->execute([$d]);
        }

        if (!empty($config['debug'])) {
            error_log("Usando SQLite fallback en: " . $sqlitePath);
        }
    } catch (Exception $e) {
        http_response_code(500);
        if (!empty($config['debug'])) {
            echo "Error inicializando SQLite fallback: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        } else {
            echo "Error de conexión a la base de datos.";
        }
        exit;
    }
}
