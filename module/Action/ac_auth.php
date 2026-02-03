<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../query/query.php';

function login_user($email, $password) {
    $user = login_user_db($email, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['username'];
        return true;
    }
    
    return false;
}

function logout_user() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
