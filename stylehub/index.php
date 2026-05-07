<?php
// ============================================================
// index.php - Home Page
// This is the main landing page of StyleHub store.
// ============================================================

session_start();
require_once 'config.php';

// --- Fetch featured products (latest 4) ---
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StyleHub - Fashion for Everyone</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<!-- ============================================================
     NAVIGATION BAR
============================================================ -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">Style<span>Hub</span></a>

        <ul class="nav-links">
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="products.php">Shop</a></li>
            <li><a href="products.php?category=Men">Men</a></li>
            <li><a href="products.php?category=Women">Women</a></li>
            <li><a href="products.php?category=Kids">Kids</a></li>
        </ul>

        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // Count cart items for badge
                $cart_count_res = mysqli_prepare($conn, "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                mysqli_stmt_bind_param($cart_count_res, "i", $_SESSION['user_id']);
                mysqli_stmt_execute($cart_count_res);
                $cart_count = mysqli_fetch_assoc(mysqli_stmt_get_result($cart_count_res))['total'] ?? 0;
                ?>
                <a href="cart.php" class="btn-icon">🛒 Cart (<?= (int)$cart_count ?>)</a>
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

<!-- ============================================================
     HERO SECTION
============================================================ -->
<section class="hero">
    <div class="hero-content">
        <p class="hero-tag">New Collection 2025</p>
        <h1>Fashion That <br><span>Speaks for You</span></h1>
        <p class="hero-desc">Explore the latest trends in Men, Women & Kids fashion. Quality clothing at affordable prices.</p>
        <div class="hero-buttons">
            <a href="products.php" class="btn-primary large">Shop Now</a>
            <a href="products.php?category=Women" class="btn-outline large">Women's Collection</a>
        </div>
    </div>
    <div class="hero-image">
        <div class="hero-badge">
            <span class="badge-num">500+</span>
            <span class="badge-text">Styles Available</span>
        </div>
    </div>
</section>

<!-- ============================================================
     CATEGORY CARDS
============================================================ -->
<section class="categories">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="category-grid">
            <a href="products.php?category=Men" class="category-card men">
                <div class="cat-overlay">
                    <h3>Men</h3>
                    <p>Shirts, Jeans & More</p>
                </div>
            </a>
            <a href="products.php?category=Women" class="category-card women">
                <div class="cat-overlay">
                    <h3>Women</h3>
                    <p>Dresses, Tops & More</p>
                </div>
            </a>
            <a href="products.php?category=Kids" class="category-card kids">
                <div class="cat-overlay">
                    <h3>Kids</h3>
                    <p>Cute & Comfortable</p>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED PRODUCTS
============================================================ -->
<section class="featured-products">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <p class="section-sub">Hand-picked styles just for you</p>

        <div class="product-grid">
            <?php while ($product = mysqli_fetch_assoc($result)): ?>
            <div class="product-card">
                <div class="product-img">
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         onerror="this.src='https://placehold.co/300x380/f5e8d5/c8975a?text=No+Image'">
                    <span class="product-badge"><?= htmlspecialchars($product['category']) ?></span>
                </div>
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="product-desc"><?= htmlspecialchars(substr($product['description'], 0, 60)) ?>...</p>
                    <div class="product-footer">
                        <span class="price"><?= CURRENCY . number_format($product['price'], 2) ?></span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="cart.php?action=add&id=<?= $product['id'] ?>" class="btn-cart">Add to Cart</a>
                        <?php else: ?>
                            <a href="login.php" class="btn-cart">Login to Buy</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="center-btn">
            <a href="products.php" class="btn-primary large">View All Products</a>
        </div>
    </div>
</section>

<!-- ============================================================
     WHY CHOOSE US
============================================================ -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">🚚</div>
                <h4>Free Delivery</h4>
                <p>On orders above ₹999</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">↩️</div>
                <h4>Easy Returns</h4>
                <p>7-day return policy</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🔒</div>
                <h4>Secure Payment</h4>
                <p>100% safe checkout</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🌟</div>
                <h4>Best Quality</h4>
                <p>Premium fabric guaranteed</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FOOTER
============================================================ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h3 class="footer-logo">Style<span>Hub</span></h3>
                <p>Your one-stop fashion destination for Men, Women, and Kids clothing.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="cart.php">Cart</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <ul>
                    <li><a href="products.php?category=Men">Men</a></li>
                    <li><a href="products.php?category=Women">Women</a></li>
                    <li><a href="products.php?category=Kids">Kids</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul>
                    <li>📧 hello@stylehub.com</li>
                    <li>📞 +91 99999 88888</li>
                    <li>📍 Ahmedabad, India</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 StyleHub. Made with ❤️ for fashion lovers.</p>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
