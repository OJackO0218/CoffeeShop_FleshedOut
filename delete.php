<?php
// delete.php
include 'auth.php';
include 'config.php';
require_owner();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
}

header("Location: index.php");
exit;
