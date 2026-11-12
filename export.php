<?php
// export.php
include 'auth.php';
include 'config.php';
require_owner();

// Force browser download as Excel file (.xls)
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Kopipiro_Orders_Report.xls");
header("Pragma: no-cache");
header("Expires: 0");

$query = "SELECT o.*, u.username as cashier_username 
          FROM orders o 
          LEFT JOIN users u ON o.cashier_id = u.id 
          ORDER BY o.id DESC";
$result = mysqli_query($conn, $query);
?>
<table border="1">
    <thead>
        <tr style="background-color: #f2f2f2;">
            <th>Order ID</th>
            <th>Date</th>
            <th>Customer Name</th>
            <th>Assigned Cashier</th>
            <th>Gross Total (Rp)</th>
            <th>Discount (Rp)</th>
            <th>Final Price (Rp)</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                <td><?= htmlspecialchars($row['cashier_username'] ?? 'Unassigned') ?></td>
                <td><?= $row['total_price'] ?></td>
                <td><?= $row['discount_amount'] ?></td>
                <td><?= $row['final_price'] ?></td>
                <td><?= $row['status'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
