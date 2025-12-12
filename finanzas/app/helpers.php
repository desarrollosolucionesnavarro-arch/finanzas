<?php

/**
 * Helper functions for the application
 * @var array $_POST
 * @var array $_SESSION
 * @var array $_GET
 */

function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function flash($key, $value = null)
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if ($value === null) {
        $v = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $v;
    } else {
        $_SESSION['flash'][$key] = $value;
    }
}

function old($key, $default = '')
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['old'][$key] ?? $default;
}

function set_old_from_post()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $post = filter_input_array(INPUT_POST, FILTER_DEFAULT);
    $_SESSION['old'] = $post ?? [];
}

function require_auth()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Debes iniciar sesión para acceder a esta página.');
        header('Location: login.php');
        exit;
    }
}

function csrf_token()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['_csrf_token'])) {
        // @phpstan-ignore-next-line
        if (function_exists('random_bytes')) {
            $bytes = random_bytes(32);
            // @phpstan-ignore-next-line
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(32);
        } else {
            $bytes = openssl_random_pseudo_bytes(16);
        }
        $_SESSION['_csrf_token'] = bin2hex($bytes);
    }
    return $_SESSION['_csrf_token'];
}

function verify_csrf()
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $token = $_POST['_csrf_token'] ?? '';
    $valid = !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
    if (!$valid) {
        flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
        return false;
    }
    return true;
}

function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
}

function validate_decimal($value, $min = 0, $max = null)
{
    $val = (float)$value;
    if ($val < $min) return false;
    if ($max !== null && $val > $max) return false;
    return true;
}

function validate_date($date)
{
    $d = \DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// APCu polyfills + file fallback cache (apcu_store, apcu_fetch, apcu_delete)
if (!function_exists('apcu_store')) {
    function apcu_store($key, $value, $ttl = 0)
    {
        $dir = __DIR__ . '/../data/cache';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
        $expiry = $ttl > 0 ? time() + (int)$ttl : 9999999999;
        $payload = json_encode(['expiry' => $expiry, 'value' => $value]);
        return file_put_contents($file, $payload) !== false;
    }
}

if (!function_exists('apcu_fetch')) {
    function apcu_fetch($key, &$success = null)
    {
        $dir = __DIR__ . '/../data/cache';
        $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
        if (!is_file($file)) {
            $success = false;
            return false;
        }
        $payload = @file_get_contents($file);
        if (!$payload) {
            $success = false;
            return false;
        }
        $obj = json_decode($payload, true);
        if (!$obj || (isset($obj['expiry']) && $obj['expiry'] < time())) {
            @unlink($file);
            $success = false;
            return false;
        }
        $success = true;
        return $obj['value'] ?? null;
    }
}

if (!function_exists('apcu_delete')) {
    function apcu_delete($key)
    {
        $dir = __DIR__ . '/../data/cache';
        $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
        if (is_file($file)) return unlink($file);
        return false;
    }
}

function cache_set($key, $value, $ttl = 30)
{
    if (function_exists('apcu_store')) {
        /** @phpstan-ignore-next-line */
        return apcu_store($key, $value, $ttl);
    }
    $dir = __DIR__ . '/../data/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
    $expiry = time() + (int)$ttl;
    $payload = json_encode(['expiry' => $expiry, 'value' => $value]);
    return file_put_contents($file, $payload) !== false;
}

function cache_get($key)
{
    if (function_exists('apcu_fetch')) {
        $success = false;
        // @phpstan-ignore-next-line
        $val = apcu_fetch($key, $success);
        return $success ? $val : null;
    }
    $dir = __DIR__ . '/../data/cache';
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
    if (!is_file($file)) return null;
    $payload = @file_get_contents($file);
    if (!$payload) return null;
    $obj = json_decode($payload, true);
    if (!$obj) return null;
    if (isset($obj['expiry']) && $obj['expiry'] < time()) {
        @unlink($file);
        return null;
    }
    return $obj['value'] ?? null;
}

function cache_delete($key)
{
    if (function_exists('apcu_delete')) {
        /** @phpstan-ignore-next-line */
        return apcu_delete($key);
    }
    $dir = __DIR__ . '/../data/cache';
    $file = $dir . '/' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
    if (is_file($file)) return unlink($file);
    return false;
}
