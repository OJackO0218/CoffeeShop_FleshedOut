<?php
// confirmation_order.php
include 'auth.php';
include 'config.php';

if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$order) {
    die("Order not found.");
}

// Fetch order items with product details
$items_query = "SELECT oi.*, p.name, p.category 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$stmt_items = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);
$items = mysqli_stmt_get_result($stmt_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - #<?= $order['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Order Placed Successfully!</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <p class="mb-1"><strong>Order ID:</strong> #<?= $order['id'] ?></p>
                                <p class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <p class="mb-1"><strong>Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <span class="badge <?= $order['status'] === 'completed' ? 'bg-success' : ($order['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-3">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; while ($it = mysqli_fetch_assoc($items)): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= htmlspecialchars($it['name']) ?></td>
                                            <td><?= htmlspecialchars($it['category']) ?></td>
                                            <td>Rp <?= number_format($it['unit_price'], 0, ',', '.') ?></td>
                                            <td><?= $it['quantity'] ?></td>
                                            <td>Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                    <tr>
                                        <td colspan="5" class="text-end"><strong>Gross Total:</strong></td>
                                        <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                        <tr class="table-success">
                                            <td colspan="5" class="text-end text-success"><strong>Customer Discount:</strong></td>
                                            <td class="text-success">- Rp <?= number_format($order['discount_amount'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="table-active">
                                        <td colspan="5" class="text-end fs-5"><strong>Total Paid:</strong></td>
                                        <td class="fs-5"><strong>Rp <?= number_format($order['final_price'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-primary btn-sm">&larr; Back to Menu</a>
                            <?php if (is_logged_in()): ?>
                                <a href="order_history.php" class="btn btn-outline-secondary btn-sm">View Order History</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
