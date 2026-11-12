<?php
// order_history.php
include 'auth.php';
include 'config.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all orders for this user
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <h4 class="mb-3">My Order History</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Gross Total</th>
                                <th>Discount</th>
                                <th>Final Price</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($orders) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">You haven't placed any orders yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                                    <tr>
                                        <td>#<?= $o['id'] ?></td>
                                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                                        <td>Rp <?= number_format($o['total_price'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($o['discount_amount'] > 0): ?>
                                                <span class="text-success">- Rp <?= number_format($o['discount_amount'], 0, ',', '.') ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><strong>Rp <?= number_format($o['final_price'], 0, ',', '.') ?></strong></td>
                                        <td>
                                            <span class="badge <?= $o['status'] === 'completed' ? 'bg-success' : ($o['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                <?= htmlspecialchars($o['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="confirmation_order.php?order_id=<?= $o['id'] ?>" class="btn btn-outline-primary btn-sm">View Receipt</a>
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
