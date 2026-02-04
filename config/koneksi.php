<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$hostname = gethostname();
// echo $hostname;
// die;
switch ($hostname) {
    case $_ENV['SERVERNAME1']:
        $serverName = $_ENV['SERVERNAME1'] . '\SQLEXPRESS';
        break;

    case $_ENV['SERVERNAME2']:
        $serverName = $_ENV['SERVERNAME2'];
        break;

    default:
        $serverName = $_ENV['SERVERNAME3'];
        break;
}

$database = $_ENV['DATABASE'];
$username = $_ENV['USERNAME'];
$password = $_ENV['PASSWORD'];

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
    die(print_r(sqlsrv_errors(), true));
}
