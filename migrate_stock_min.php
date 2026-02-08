<?php
require 'config/koneksi.php';
$sql = 'ALTER TABLE item_table ADD stock_min INT DEFAULT 0';
$stmt = sqlsrv_query($koneksi, $sql);
if($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo 'Column stock_min added successfully';
}
?>
