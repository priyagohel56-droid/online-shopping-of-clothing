<?php
// ============================================================
// admin.php - Admin Panel
// Only admins can access this page.
// ============================================================

session_start();
require_once 'config.php';

// Block non-admins
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$message = '';

// --- HANDLE DELETE PRODUCT ---
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $del    = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($del, "i", $del_id);
    mysqli_stmt_execute($del);
    $message = "✅ Product deleted successfully!";
}

// --- HANDLE ADD / EDIT PRODUCT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pid      = isset($_POST['pid']) ? (int)$_POST['pid'] : 0;
    $name     = trim($_POST['name']);
    $desc     = trim($_POST['description']);
    $price    = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $stock    = (int)$_POST['stock'];
    $image    = trim($_POST['image']) ?: 'placeholder.jpg';

    if (empty($name) || $price <= 0) {
        $message = "❌ Please fill in all required fields.";
    } else {
        if ($pid > 0) {
            $stmt = mysqli_prepare($conn,
                "UPDATE products SET name=?, description=?, price=?, category=?, image=?, stock=? WHERE id=?"
            );
            mysqli_stmt_bind_param($stmt, "ssdssii", $name, $desc, $price, $category, $image, $stock, $pid);
        } else {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO products (name, description, price, category, image, stock) VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "ssdssi", $name, $desc, $price, $category, $image, $stock);
        }

        if (mysqli_stmt_execute($stmt)) {
            $message = $pid > 0 ? "✅ Product updated successfully!" : "✅ Product added successfully!";
        } else {
            $message = "❌ Error: " . mysqli_error($conn);
        }
    }
}

// --- Fetch product for editing ---
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id      = (int)$_GET['edit'];
    $edit_stmt    = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
    mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
    mysqli_stmt_execute($edit_stmt);
    $edit_product = mysqli_fetch_assoc(mysqli_stmt_get_result($edit_stmt));
}

// --- Fetch all products ---
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");

// --- Fetch stats ---
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$total_users    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$rev_row        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as s FROM orders"));
$total_revenue  = $rev_row['s'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - StyleHub</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<!-- Admin Navbar -->
<nav class="navbar admin-nav">
    <div class="nav-container">
        <a href="index.php" class="logo">Style<span>Hub</span> <small>Admin</small></a>
        <div class="nav-right">
            <a href="index.php" class="btn-outline">View Store</a>
            <a href="products.php" class="btn-outline">Products Page</a>
            <a href="logout.php" class="btn-primary">Logout</a>
        </div>
    </div>
</nav>

<div class="admin-wrapper">

    <!-- Stats Cards -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <h3><?= $total_products ?></h3>
                <p>Products</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <h3><?= $total_orders ?></h3>
                <p>Orders</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?= $total_users ?></h3>
                <p>Users</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3><?= CURRENCY . number_format($total_revenue, 0) ?></h3>
                <p>Revenue</p>
            </div>
        </div>
    </div>

    <!-- Status Message -->
    <?php if ($message): ?>
        <div class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="admin-layout">

        <!-- Add / Edit Product Form -->
        <div class="admin-form-section">
            <h3><?= $edit_product ? '✏️ Edit Product' : '➕ Add New Product' ?></h3>

            <form method="POST" action="admin.php" class="admin-form">
                <input type="hidden" name="pid" value="<?= $edit_product ? $edit_product['id'] : 0 ?>">

                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" placeholder="e.g. Classic White Tee" required
                           value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Product description..."><?= $edit_product ? htmlspecialchars($edit_product['description']) : '' ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Price (₹) *</label>
                        <input type="number" name="price" step="0.01" min="0" placeholder="e.g. 599.00" required
                               value="<?= $edit_product ? $edit_product['price'] : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" min="0" placeholder="e.g. 20"
                               value="<?= $edit_product ? $edit_product['stock'] : '10' ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="Men"    <?= ($edit_product && $edit_product['category'] == 'Men')    ? 'selected' : '' ?>>Men</option>
                            <option value="Women"  <?= ($edit_product && $edit_product['category'] == 'Women')  ? 'selected' : '' ?>>Women</option>
                            <option value="Kids"   <?= ($edit_product && $edit_product['category'] == 'Kids')   ? 'selected' : '' ?>>Kids</option>
                            <option value="Unisex" <?= ($edit_product && $edit_product['category'] == 'Unisex') ? 'selected' : '' ?>>Unisex</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Image Filename</label>
                        <input type="text" name="image" placeholder="e.g. shirt.jpg"
                               value="<?= $edit_product ? htmlspecialchars($edit_product['image']) : '' ?>">
                    </div>
                </div>

                <div class="admin-form-btns">
                    <button type="submit" class="btn-primary">
                        <?= $edit_product ? 'Update Product' : 'Add Product' ?>
                    </button>
                    <?php if ($edit_product): ?>
                        <a href="admin.php" class="btn-outline">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="admin-table-section">
            <h3>📦 All Products</h3>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td>
                                <img src="uploads/<?= htmlspecialchars($p['image']) ?>"
                                     alt="<?= htmlspecialchars($p['name']) ?>"
                                     onerror="this.src='https://placehold.co/48x48/f5e8d5/c8975a?text=?'"
                                     class="table-img">
                            </td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><span class="category-tag"><?= htmlspecialchars($p['category']) ?></span></td>
                            <td><?= CURRENCY . number_format($p['price'], 2) ?></td>
                            <td><?= $p['stock'] ?></td>
                            <td class="action-btns">
                                <a href="admin.php?edit=<?= $p['id'] ?>" class="btn-edit">Edit</a>
                                <a href="admin.php?delete=<?= $p['id'] ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this product?')">
                                   Delete
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- end admin-layout -->

    <!-- Recent Orders Table -->
    <div class="admin-orders">
        <h3>📋 Recent Orders</h3>
        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $orders = mysqli_query($conn,
                        "SELECT orders.*, users.name AS customer_name
                         FROM orders
                         JOIN users ON orders.user_id = users.id
                         ORDER BY orders.id DESC LIMIT 10"
                    );
                    if ($orders && mysqli_num_rows($orders) > 0):
                        while ($order = mysqli_fetch_assoc($orders)):
                    ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= htmlspecialchars($order['phone']) ?></td>
                        <td><?= CURRENCY . number_format($order['total_amount'], 2) ?></td>
                        <td><span class="status-badge"><?= htmlspecialchars($order['status']) ?></span></td>
                        <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-light);">No orders yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- end admin-wrapper -->

<script src="script.js"></script>
</body>
</html>
