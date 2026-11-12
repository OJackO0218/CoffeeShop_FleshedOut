<?php
// navbar.php
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Kopipiro</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Menu</a>
                </li>
                <?php if (is_owner()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="owner_dashboard.php">Owner Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Manage Users</a>
                    </li>
                <?php endif; ?>
                <?php if (is_cashier()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cashier_orders.php">Cashier Orders</a>
                    </li>
                <?php endif; ?>
                <?php if (is_customer()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="order_history.php">My Orders</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (!is_owner() && !is_cashier()): ?>
                    <a href="cart.php" class="btn btn-outline-warning btn-sm position-relative me-2">
                        Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cart_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <?php if (is_logged_in()): ?>
                    <span class="text-white small">
                        <?= htmlspecialchars($_SESSION['username']) ?>
                        <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    </span>
                    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm">Login</a>
                    <a href="register.php" class="btn btn-warning btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
