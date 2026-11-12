<?php
// auth.php — include at the top of protected pages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_owner() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'owner';
}

function is_cashier() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'cashier';
}

function is_customer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'customer';
}

function require_owner() {
    if (!is_owner()) {
        http_response_code(403);
        die("403 - Access denied. This page is for the Owner only.");
    }
}

function require_cashier() {
    if (!is_cashier()) {
        http_response_code(403);
        die("403 - Access denied. This page is for Cashiers only.");
    }
}