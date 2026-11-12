<?php
// edit.php
include 'auth.php';
include 'config.php';
require_owner();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Product not found.");
}

$error = "";

if (isset($_POST['update'])) {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $price = intval($_POST['price']);
    $rating = floatval($_POST['rating']);
    $image_path = $data['image'];

    // If new file is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . $file_name;

        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($image_type, $allowed)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_path = trim($_POST['image_url']);
    }

    if (empty($name) || $price <= 0) {
        $error = "Name and valid price are required.";
    } else {
        $stmt_update = mysqli_prepare($conn, "UPDATE products SET name = ?, category = ?, description = ?, price = ?, rating = ?, image = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "sssidsi", $name, $category, $description, $price, $rating, $image_path, $id);
        mysqli_stmt_execute($stmt_update);

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Edit Product</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="coffee" <?= $data['category'] === 'coffee' ? 'selected' : '' ?>>Coffee</option>
                                <option value="non-coffee" <?= $data['category'] === 'non-coffee' ? 'selected' : '' ?>>Non-Coffee</option>
                                <option value="toast" <?= $data['category'] === 'toast' ? 'selected' : '' ?>>Toast</option>
                                <option value="food" <?= $data['category'] === 'food' ? 'selected' : '' ?>>Food</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (Rp)</label>
                            <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($data['price']) ?>" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <input type="number" step="0.1" class="form-control" id="rating" name="rating" min="1" max="5" value="<?= htmlspecialchars($data['rating']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Upload New Image File (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                        <div class="mb-4">
                            <label for="image_url" class="form-label">Or Current / New Image URL</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($data['image'] ?? '') ?>">
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="update" class="btn btn-warning">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
