<?php
// index.php
include 'auth.php';
include 'config.php';

// Cart in session for multiple product orders
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
$message = "";
if (isset($_POST['add_to_cart'])) {
    $id = intval($_POST['product_id']);
    $qty = max(1, intval($_POST['qty']));
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
    $message = "Added to cart! <a href='cart.php' class='alert-link'>View Cart</a>";
}

// Category filter
$category = $_GET['category'] ?? 'all';
$valid_categories = ['coffee', 'non-coffee', 'toast', 'food'];

if (in_array($category, $valid_categories)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? ORDER BY id ASC");
    mysqli_stmt_bind_param($stmt, "s", $category);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $category = 'all';
    $result = mysqli_query($conn, "SELECT * FROM products ORDER BY id ASC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopipiro - Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 mb-5">
        <?php if ($message): ?>
            <div class="alert alert-success py-2 mb-3">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Category Filters -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="btn-group" role="group">
                <a href="index.php?category=all" class="btn btn-outline-dark btn-sm <?= $category === 'all' ? 'active' : '' ?>">All</a>
                <a href="index.php?category=coffee" class="btn btn-outline-dark btn-sm <?= $category === 'coffee' ? 'active' : '' ?>">Coffee</a>
                <a href="index.php?category=non-coffee" class="btn btn-outline-dark btn-sm <?= $category === 'non-coffee' ? 'active' : '' ?>">Non-Coffee</a>
                <a href="index.php?category=toast" class="btn btn-outline-dark btn-sm <?= $category === 'toast' ? 'active' : '' ?>">Toast</a>
            </div>
            <?php if (is_owner()): ?>
                <a href="create.php" class="btn btn-success btn-sm">+ Add Product</a>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Kopipiro Menu</h4>
                <span class="badge bg-light text-dark text-capitalize"><?= htmlspecialchars($category) ?></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Rating</th>
                                <th>Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="<?= htmlspecialchars($row['image']) ?>" width="75" height="55" style="object-fit: cover;" class="rounded border" alt="product">
                                    <?php else: ?>
                                        <span class="text-muted small">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['category']) ?></span></td>
                                <td><small class="text-muted"><?= htmlspecialchars($row['description'] ?? '') ?></small></td>
                                <td>⭐ <?= htmlspecialchars($row['rating']) ?></td>
                                <td>
                                    <?php if (is_customer() && $row['category'] === 'coffee'): ?>
                                        <strike class="text-muted">Rp <?= number_format($row['price'], 0, ',', '.') ?></strike><br>
                                        <strong class="text-success">Rp <?= number_format(round($row['price'] * 0.9), 0, ',', '.') ?></strong>
                                    <?php else: ?>
                                        Rp <?= number_format($row['price'], 0, ',', '.') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (is_owner()): ?>
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    <?php elseif (is_cashier()): ?>
                                        <span class="text-muted small">Cashier</span>
                                    <?php else: ?>
                                        <form method="POST" action="" class="d-inline-flex align-items-center gap-1">
                                            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                            <input type="number" name="qty" value="1" min="1" max="20" class="form-control form-control-sm text-center" style="width: 55px;" required>
                                            <button type="submit" name="add_to_cart" class="btn btn-success btn-sm">Order</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
