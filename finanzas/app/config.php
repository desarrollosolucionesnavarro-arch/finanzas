<?php
// Ajusta según tu entorno
return [
    // Prefer explicit loopback IP to avoid socket/hostname resolution quirks
    'db_host' => '127.0.0.1',
    'db_name' => 'finanzas',
    'db_user' => 'root',
    'db_pass' => '',
    'timezone' => 'America/Bogota',
    'db_port' => 3306,
    'debug' => true,
    // Usar conexión persistente PDO (true/false). Desactivar para entornos de desarrollo en Windows.
    'db_persistent' => false,
];
