<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/module/Action/ac_auth.php';

function handle_login() {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if ($email === '' && $password === '') {
            $error = 'Email/Username dan Password wajib diisi!';
        } elseif ($email === '') {
            $error = 'Email/Username wajib diisi!';
        } elseif ($password === '') {
            $error = 'Password wajib diisi!';
        } else {
            if (login_user($email, $password)) {
                header('Location: index.php');
                exit();
            } else {
                $error = 'Email/Username atau Password salah, atau koneksi database gagal!';
            }
        }
    }
    return $error;
}

function require_auth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function resolve_route() {
    $allowed_pages = ['dashboard', 'log_data', 'log_data_create', 'location', 'location-form', 'in_out', 'in_out_history', 'adjustment_history'];
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    if (!in_array($page, $allowed_pages)) {
        $page = 'dashboard';
    }
    if ($page == 'log_data') {
        $GLOBALS['content_file'] = "module/md_item.php";
        $GLOBALS['scripts_file'] = "ui/scripts_location.php";
    } elseif ($page == 'log_data_create') {
        $GLOBALS['content_file'] = "module/md_item_insert.php";
        $GLOBALS['scripts_file'] = "ui/scripts_location.php";
    } elseif ($page == 'location') {
        $GLOBALS['content_file'] = "module/md_location.php";
        $GLOBALS['scripts_file'] = "ui/scripts_location.php";
    } elseif ($page == 'location-form') {
        $GLOBALS['content_file'] = "ui/pages/Location/form.php";
        $GLOBALS['scripts_file'] = "ui/scripts_location.php";
    } elseif ($page == 'in_out') {
        $GLOBALS['content_file'] = "module/md_inventory_log.php";
        $GLOBALS['scripts_file'] = "ui/scripts.php";
    } elseif ($page == 'in_out_history') {
        $GLOBALS['content_file'] = "module/md_inventory_history.php";
        $GLOBALS['scripts_file'] = "ui/scripts.php";
    } elseif ($page == 'adjustment_history') {
        $GLOBALS['content_file'] = "module/md_adjustment_history.php";
        $GLOBALS['scripts_file'] = "ui/scripts.php";
    } else {
        $GLOBALS['content_file'] = "module/md_dashboard.php";
        $GLOBALS['scripts_file'] = "ui/scripts.php";
    }
    return $page;
}

resolve_route();
?>