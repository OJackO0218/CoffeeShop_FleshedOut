<?php
// owner_dashboard.php
include 'auth.php';
include 'config.php';
require_owner();

// Fetch summary metrics
$res_total = mysqli_query($conn, "SELECT COUNT(*) as total_orders, COALESCE(SUM(final_price), 0) as total_sales FROM orders WHERE status = 'completed'");
$summary = mysqli_fetch_assoc($res_total);

// Fetch sales by date for Chart.js
$chart_query = "SELECT DATE(created_at) as order_date, SUM(final_price) as daily_total 
                FROM orders 
                WHERE status = 'completed' 
                GROUP BY DATE(created_at) 
                ORDER BY order_date ASC 
                LIMIT 7";
$chart_res = mysqli_query($conn, $chart_query);

$dates = [];
$totals = [];
while ($row = mysqli_fetch_assoc($chart_res)) {
    $dates[] = $row['order_date'];
    $totals[] = (int)$row['daily_total'];
}

// Fetch all orders with cashier and customer info
$orders_query = "SELECT o.*, u.username as cashier_username 
                 FROM orders o 
                 LEFT JOIN users u ON o.cashier_id = u.id 
                 ORDER BY o.id DESC";
$orders = mysqli_query($conn, $orders_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Kopipiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Owner Dashboard</h4>
            <div>
                <a href="export.php" class="btn btn-success btn-sm">Download Excel Report</a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-primary">
                    <div class="card-body">
                        <h6 class="text-muted">Total Completed Sales</h6>
                        <h3 class="fw-bold text-primary mb-0">Rp <?= number_format($summary['total_sales'], 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-success">
                    <div class="card-body">
                        <h6 class="text-muted">Total Completed Orders</h6>
                        <h3 class="fw-bold text-success mb-0"><?= $summary['total_orders'] ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Completed Sales Chart</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="90"></canvas>
            </div>
        </div>

        <!-- All Orders Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">All Customer Orders</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Assigned Cashier</th>
                                <th>Gross Total</th>
                                <th>Discount</th>
                                <th>Final Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($orders) === 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-3 text-muted">No orders found.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                                    <tr>
                                        <td>#<?= $o['id'] ?></td>
                                        <td><?= htmlspecialchars($o['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($o['cashier_username'] ?? 'Unassigned') ?></td>
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
                                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                                        <td class="text-center">
                                            <a href="confirmation_order.php?order_id=<?= $o['id'] ?>" class="btn btn-outline-primary btn-sm">Receipt</a>
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

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($dates) ?>,
                datasets: [{
                    label: 'Daily Sales (Rp)',
                    data: <?= json_encode($totals) ?>,
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
