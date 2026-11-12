<?php
// checkout.php
include 'auth.php';
include 'config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

$ids = implode(',', array_map('intval', array_keys($cart)));
$result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");

$items_data = [];
$total_price = 0;
$total_discount = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $qty = $cart[$row['id']];
    $raw_subtotal = $row['price'] * $qty;
    $total_price += $raw_subtotal;

    // 10% discount on coffee for logged-in customers
    $is_discounted = is_customer() && ($row['category'] === 'coffee');
    $discount = $is_discounted ? round($raw_subtotal * 0.10) : 0;
    $total_discount += $discount;

    $final_subtotal = $raw_subtotal - $discount;

    $items_data[] = [
        'product_id' => $row['id'],
        'name' => $row['name'],
        'category' => $row['category'],
        'price' => $row['price'],
        'qty' => $qty,
        'raw_subtotal' => $raw_subtotal,
        'discount' => $discount,
        'final_subtotal' => $final_subtotal
    ];
}

$final_price = $total_price - $total_discount;

// Handle Order Submission
if (isset($_POST['place_order'])) {
    $user_id = is_logged_in() ? $_SESSION['user_id'] : null;
    $customer_name = is_logged_in() ? $_SESSION['username'] : trim($_POST['customer_name']);

    if (empty($customer_name)) {
        $customer_name = "Guest";
    }

    // Insert into orders table
    $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, customer_name, total_price, discount_amount, final_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "isiii", $user_id, $customer_name, $total_price, $total_discount, $final_price);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

    // Insert into order_items table
    $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
    foreach ($items_data as $item) {
        mysqli_stmt_bind_param($stmt_item, "iiiii", $order_id, $item['product_id'], $item['qty'], $item['price'], $item['final_subtotal']);
        mysqli_stmt_execute($stmt_item);
    }

    // Clear cart
    unset($_SESSION['cart']);

    header("Location: confirmation_order.php?order_id=" . $order_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <h4 class="mb-3">Checkout</h4>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                                <th>Discount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($items_data as $it): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($it['name']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($it['category']) ?></span></td>
                                    <td>Rp <?= number_format($it['price'], 0, ',', '.') ?></td>
                                    <td><?= $it['qty'] ?></td>
                                    <td>Rp <?= number_format($it['raw_subtotal'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($it['discount'] > 0): ?>
                                            <span class="text-success">- Rp <?= number_format($it['discount'], 0, ',', '.') ?> (10%)</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><strong>Rp <?= number_format($it['final_subtotal'], 0, ',', '.') ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="7" class="text-end"><strong>Gross Total:</strong></td>
                                <td>Rp <?= number_format($total_price, 0, ',', '.') ?></td>
                            </tr>
                            <?php if ($total_discount > 0): ?>
                                <tr class="table-success">
                                    <td colspan="7" class="text-end text-success"><strong>Customer Coffee Discount:</strong></td>
                                    <td class="text-success">- Rp <?= number_format($total_discount, 0, ',', '.') ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr class="table-active">
                                <td colspan="7" class="text-end fs-5"><strong>Final Amount to Pay:</strong></td>
                                <td class="fs-5"><strong>Rp <?= number_format($final_price, 0, ',', '.') ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="">
                    <?php if (!is_logged_in()): ?>
                        <div class="mb-3" style="max-width: 320px;">
                            <label for="customer_name" class="form-label">Customer Name (Guest):</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter your name" required>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-3">Ordering as: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (<?= htmlspecialchars($_SESSION['role']) ?>)</p>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between">
                        <a href="cart.php" class="btn btn-secondary btn-sm">&larr; Back to Cart</a>
                        <button type="submit" name="place_order" class="btn btn-success">Confirm & Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
