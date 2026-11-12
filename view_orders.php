<?php
// view_orders.php
include 'auth.php';

if (is_owner()) {
    header("Location: owner_dashboard.php");
    exit;
} elseif (is_cashier()) {
    header("Location: cashier_orders.php");
    exit;
} elseif (is_logged_in()) {
    header("Location: order_history.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}
