<?php
/**
 * PHP Standard Library Stubs
 * For IDE/Analyzer compatibility
 */

// Throwable interface (base for Exception and Error)
interface Throwable
{
    /** @return string */
    public function getMessage();

    /** @return int */
    public function getCode();

    /** @return string */
    public function getFile();

    /** @return int */
    public function getLine();

    /** @return array */
    public function getTrace();

    /** @return Throwable|null */
    public function getPrevious();

    /** @return string */
    public function getTraceAsString();

    /** @return string */
    public function __toString();
}

// Exception class (part of standard PHP)
class Exception implements Throwable
{
    /** @return string */
    public function getMessage() { }

    /** @return int */
    public function getCode() { }

    /** @return string */
    public function getFile() { }

    /** @return int */
    public function getLine() { }

    /** @return array */
    public function getTrace() { }

    /** @return Throwable|null */
    public function getPrevious() { }

    /** @return string */
    public function getTraceAsString() { }

    /** @return string */
    public function __toString() { }
}

class PDOException extends Exception { }

// PDO Classes
class PDO
{
    const ATTR_ERRMODE = 3;
    const ATTR_DEFAULT_FETCH_MODE = 19;
    const ATTR_TIMEOUT = 28;
    const FETCH_ASSOC = 2;
    const ERRMODE_EXCEPTION = 2;

    /** @param string $dsn */
    public function __construct($dsn, $username = null, $password = null, $options = []) { }

    /** @return PDOStatement|false */
    public function prepare($query) { }

    /** @return bool */
    public function query($query) { }

    /** @return string|false */
    public function lastInsertId() { }

    /** @return mixed */
    public function exec($query) { }

    /** @return mixed */
    public function setAttribute($attribute, $value) { }
}

class PDOStatement
{
    /** @return bool */
    public function execute($params = []) { }

    /** @return mixed */
    public function fetch($fetch_style = null) { }

    /** @return array */
    public function fetchAll($fetch_style = null) { }

    /** @return array */
    public function fetchColumn($column_number = 0) { }
}

if (!function_exists('random_bytes')) {
    /**
     * Generate cryptographically secure random bytes
     * @param int $length
     * @return string
     */
    function random_bytes(int $length): string { return ""; }
}

if (!function_exists('openssl_random_pseudo_bytes')) {
    /**
     * @param int $length
     * @param bool|null $crypto_strong
     * @return string
     */
    function openssl_random_pseudo_bytes(int $length, &$crypto_strong = null): string { return ""; }
}

if (!function_exists('function_exists')) {
    function function_exists(string $function_name): bool { return false; }
}

