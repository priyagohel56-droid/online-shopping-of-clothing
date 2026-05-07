<?php
// ============================================================
// products.php - All Products Page
// Shows all products with optional category filter.
// ============================================================

session_start();
require_once 'config.php';

// --- Get category filter from URL ---
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// --- Build SQL query ---
if (!empty($category)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category = ? ORDER BY id DESC");
    mysqli_stmt_bind_param($stmt, "s", $category);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
}

// --- Cart count for navbar badge ---
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $cc = mysqli_prepare($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($cc, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($cc);
    $cart_count = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($cc))['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - StyleHub</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">Style<span>Hub</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php" class="active">Shop</a></li>
            <li><a href="products.php?category=Men">Men</a></li>
            <li><a href="products.php?category=Women">Women</a></li>
            <li><a href="products.php?category=Kids">Kids</a></li>
        </ul>
        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="cart.php" class="btn-icon">🛒 Cart (<?= $cart_count ?>)</a>
                <?php if ($_SESSION['is_admin']): ?>
                    <a href="admin.php" class="btn-outline">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn-primary">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-outline">Login</a>
                <a href="register.php" class="btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <h1><?= $category ? htmlspecialchars($category) . "'s Collection" : "All Products" ?></h1>
    <p>Discover our latest styles</p>
</div>

<!-- Filter Buttons -->
<div class="container">
    <div class="filter-bar">
        <a href="products.php" class="filter-btn <?= empty($category) ? 'active' : '' ?>">All</a>
        <a href="products.php?category=Men" class="filter-btn <?= $category == 'Men' ? 'active' : '' ?>">Men</a>
        <a href="products.php?category=Women" class="filter-btn <?= $category == 'Women' ? 'active' : '' ?>">Women</a>
        <a href="products.php?category=Kids" class="filter-btn <?= $category == 'Kids' ? 'active' : '' ?>">Kids</a>
        <a href="products.php?category=Unisex" class="filter-btn <?= $category == 'Unisex' ? 'active' : '' ?>">Unisex</a>
    </div>
</div>

<!-- Products Grid -->
<section class="products-section">
    <div class="container">

        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="empty-state">
                <div class="empty-icon">👕</div>
                <h3>No products found</h3>
                <p>Try a different category</p>
                <a href="products.php" class="btn-primary">View All</a>
            </div>

        <?php else: ?>
            <div class="product-grid">
                <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">
                    <div class="product-img">
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             onerror="this.src='https://placehold.co/300x380/f5e8d5/c8975a?text=No+Image'">
                        <span class="product-badge"><?= htmlspecialchars($product['category']) ?></span>
                        <?php if ($product['stock'] <= 3): ?>
                            <span class="stock-warning">Only <?= $product['stock'] ?> left!</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars(substr($product['description'], 0, 70)) ?>...</p>
                        <div class="product-footer">
                            <span class="price"><?= CURRENCY . number_format($product['price'], 2) ?></span>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="cart.php?action=add&id=<?= $product['id'] ?>"
                                   class="btn-cart"
                                   onclick="showToast('Added to cart!')">
                                    Add to Cart
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn-cart">Login to Buy</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Toast -->
<div id="toast" class="toast hidden">✅ Added to cart!</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 StyleHub. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
