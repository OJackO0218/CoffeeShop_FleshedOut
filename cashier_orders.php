<?php
// cashier_orders.php
include 'auth.php';
include 'config.php';
require_cashier();

$cashier_id = $_SESSION['user_id'];
$message = "";

// Handle claiming an unassigned order
if (isset($_POST['claim_order'])) {
    $order_id = intval($_POST['order_id']);
    $stmt = mysqli_prepare($conn, "UPDATE orders SET cashier_id = ? WHERE id = ? AND cashier_id IS NULL");
    mysqli_stmt_bind_param($stmt, "ii", $cashier_id, $order_id);
    mysqli_stmt_execute($stmt);
    $message = "<div class='alert alert-success py-2'>Order #$order_id is now assigned to you.</div>";
}

// Handle status change
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    if (in_array($new_status, ['pending', 'completed', 'cancelled'])) {
        $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ? AND cashier_id = ?");
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $order_id, $cashier_id);
        mysqli_stmt_execute($stmt);
        $message = "<div class='alert alert-info py-2'>Order #$order_id updated to $new_status.</div>";
    }
}

// 1. My assigned orders (Cashiers can only view orders assigned/managed by them)
$stmt_my = mysqli_prepare($conn, "SELECT * FROM orders WHERE cashier_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt_my, "i", $cashier_id);
mysqli_stmt_execute($stmt_my);
$my_orders = mysqli_stmt_get_result($stmt_my);

// 2. Unassigned incoming orders (available to take)
$unassigned = mysqli_query($conn, "SELECT * FROM orders WHERE cashier_id IS NULL ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <h4 class="mb-3">Cashier Order Management</h4>
        <?= $message ?>

        <!-- My Managed Orders -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">My Assigned Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Final Price</th>
                                <th>Date</th>
                                <th>Current Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($my_orders) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">You have no assigned orders yet. Claim incoming orders below.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($my_orders)): ?>
                                    <tr>
                                        <td>#<?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                        <td>Rp <?= number_format($row['final_price'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                                        <td>
                                            <span class="badge <?= $row['status'] === 'completed' ? 'bg-success' : ($row['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="" class="d-inline-flex gap-1">
                                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                                <select name="status" class="form-select form-select-sm" style="width: 120px;">
                                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                                    <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                                    <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                                                <a href="confirmation_order.php?order_id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm">Details</a>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Unassigned Orders to Claim -->
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Incoming Orders (Unassigned)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Final Price</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($unassigned) === 0): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No incoming unassigned orders.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($un = mysqli_fetch_assoc($unassigned)): ?>
                                    <tr>
                                        <td>#<?= $un['id'] ?></td>
                                        <td><?= htmlspecialchars($un['customer_name']) ?></td>
                                        <td>Rp <?= number_format($un['final_price'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($un['created_at']) ?></td>
                                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($un['status']) ?></span></td>
                                        <td class="text-center">
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $un['id'] ?>">
                                                <button type="submit" name="claim_order" class="btn btn-success btn-sm">Take Order</button>
                                                <a href="confirmation_order.php?order_id=<?= $un['id'] ?>" class="btn btn-outline-secondary btn-sm">Details</a>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
