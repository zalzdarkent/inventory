<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env manually to avoid $_SERVER conflicts with Windows environment variables
$envFile = __DIR__ . '/../.env';
$env = [];

if (file_exists($envFile)) {
    $lines = file($envFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && strpos($line, '=') !== false && $line[0] !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}

$hostname = gethostname();

switch ($hostname) {
    case $env['SERVERNAME1'] ?? null:
        $serverName = $env['SERVERNAME1'] . '\SQLEXPRESS';
        break;

    case $env['SERVERNAME2'] ?? null:
        $serverName = $env['SERVERNAME2'];
        break;

    default:
        $serverName = $env['SERVERNAME3'] ?? 'localhost';
        break;
}

// Database credentials from parsed .env
$database = $env['DATABASE'] ?? 'inventory';
$username = $env['USERNAME'] ?? 'sa';
$password = $env['PASSWORD'] ?? '';

$connectionInfo = array(
    "Database" => $database,
    "UID" => $username,
    "PWD" => $password,
    "CharacterSet" => "UTF-8",
    "Encrypt" => true,
    "TrustServerCertificate" => true
);


$koneksi = sqlsrv_connect($serverName, $connectionInfo);
// var_dump($koneksi);
// die;

//  $sql = "SELECT id, item_code, name, location_id, qty, created_at, updated_at FROM item_table";
//     $stmt = sqlsrv_query($koneksi, $sql);
//     $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

//     var_dump($row);die();


if (!$koneksi) {
    if (defined('AJAX_MODE') && AJAX_MODE === true) {
        $errors = sqlsrv_errors();
        $message = "Database connection failed: ";
        foreach ($errors as $error) {
            $message .= "[SQLSTATE " . $error['SQLSTATE'] . "] " . $error['message'] . " ";
        }
        throw new Exception($message);
    } else {
        die(print_r(sqlsrv_errors(), true));
    }
}
