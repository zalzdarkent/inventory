<?php
if (function_exists('sqlsrv_connect')) {
    echo "sqlsrv_connect tersedia!";
} else {
    echo "sqlsrv_connect TIDAK tersedia!";
}
?>