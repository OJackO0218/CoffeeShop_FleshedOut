<?php
// cart.php
include 'auth.php';
include 'config.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Update quantity or remove item
if (isset($_POST['update_cart'])) {
    $product_id = intval($_POST['product_id']);
    $qty = intval($_POST['qty']);

    if ($qty <= 0) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['remove'])) {
    $product_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit;
}

if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// Fetch products in cart
$cart_items = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($ids)");
    while ($row = mysqli_fetch_assoc($result)) {
        $qty = $_SESSION['cart'][$row['id']];
        $subtotal = $row['price'] * $qty;
        $total_price += $subtotal;
        $row['qty'] = $qty;
        $row['subtotal'] = $subtotal;
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <h4 class="mb-3">Shopping Cart</h4>

        <?php if (empty($cart_items)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">Your cart is currently empty.</p>
                    <a href="index.php" class="btn btn-primary btn-sm">&larr; Back to Menu</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th style="width: 150px;">Quantity</th>
                                    <th>Subtotal</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><img src="<?= htmlspecialchars($item['image']) ?>" width="60" alt="product"></td>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-flex gap-1">
                                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" max="50" class="form-control form-control-sm text-center" required>
                                                <button type="submit" name="update_cart" class="btn btn-secondary btn-sm">Update</button>
                                            </form>
                                        </td>
                                        <td>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove item?')">Remove</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><strong>Rp <?= number_format($total_price, 0, ',', '.') ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <form method="POST" action="">
                            <button type="submit" name="clear_cart" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear entire cart?')">Clear Cart</button>
                        </form>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Continue Shopping</a>
                            <a href="checkout.php" class="btn btn-success btn-sm">Proceed to Checkout &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
