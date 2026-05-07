<?php
// ============================================================
// cart.php - Shopping Cart Page
// Images use direct URL links — no uploads folder needed!
// Just paste any image URL from internet in Admin Panel.
// ============================================================

session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$action = isset($_GET['action']) ? $_GET['action'] : '';
$pid    = isset($_GET['id'])     ? (int)$_GET['id'] : 0;

// ADD
if ($action == 'add' && $pid > 0) {
    $stock_res = mysqli_prepare($conn, "SELECT stock FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stock_res, "i", $pid);
    mysqli_stmt_execute($stock_res);
    $stock_row = mysqli_fetch_assoc(mysqli_stmt_get_result($stock_res));
    if ($stock_row && $stock_row['stock'] > 0) {
        $check = mysqli_prepare($conn, "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        mysqli_stmt_bind_param($check, "ii", $user_id, $pid);
        mysqli_stmt_execute($check);
        $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check));
        if ($existing) {
            $upd = mysqli_prepare($conn, "UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
            mysqli_stmt_bind_param($upd, "i", $existing['id']);
            mysqli_stmt_execute($upd);
        } else {
            $ins = mysqli_prepare($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
            mysqli_stmt_bind_param($ins, "ii", $user_id, $pid);
            mysqli_stmt_execute($ins);
        }
    }
    header("Location: cart.php"); exit();
}

// REMOVE
if ($action == 'remove' && $pid > 0) {
    $del = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($del, "ii", $user_id, $pid);
    mysqli_stmt_execute($del);
    header("Location: cart.php"); exit();
}

// UPDATE QTY
if ($action == 'update' && $pid > 0 && isset($_GET['qty'])) {
    $qty = max(1, (int)$_GET['qty']);
    $upd = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    mysqli_stmt_bind_param($upd, "iii", $qty, $user_id, $pid);
    mysqli_stmt_execute($upd);
    header("Location: cart.php"); exit();
}

// FETCH cart items
$cart_query = mysqli_prepare($conn,
    "SELECT cart.id, cart.quantity,
            products.name, products.price, products.image,
            products.id AS pid, products.description, products.category
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.user_id = ?"
);
mysqli_stmt_bind_param($cart_query, "i", $user_id);
mysqli_stmt_execute($cart_query);
$cart_items = mysqli_stmt_get_result($cart_query);

$total = 0;
$items = [];
while ($item = mysqli_fetch_assoc($cart_items)) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $items[] = $item;
}

$delivery    = ($total >= 999) ? 0 : 50;
$grand_total = $total + $delivery;

// ============================================================
// IMAGE HELPER FUNCTION
// How it works:
//   - If image starts with https:// → use it directly as URL
//   - If image is empty             → show a nice placeholder
//   - If image is a filename.jpg    → look in uploads/ folder
// ============================================================
function getImageSrc($image, $name = 'Product') {
    if (empty($image)) {
        return 'https://placehold.co/400x300/f5e8d5/c8975a?text=' . urlencode($name);
    }
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }
    return 'uploads/' . $image;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - StyleHub</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ── Cart Cards Grid ── */
        .cart-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Single product card */
        .cart-product-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .cart-product-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }

        /* Image box */
        .cart-card-img {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            background: #f5ece0;
        }
        .cart-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }
        .cart-product-card:hover .cart-card-img img {
            transform: scale(1.05);
        }

        /* Category badge on image */
        .cart-card-badge {
            position: absolute;
            top: 0.65rem;
            left: 0.65rem;
            background: var(--primary);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.22rem 0.6rem;
            border-radius: 100px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Remove X button top-right */
        .cart-card-remove {
            position: absolute;
            top: 0.6rem;
            right: 0.6rem;
            background: rgba(255,255,255,0.92);
            color: var(--error);
            border: 1.5px solid #fde8e8;
            border-radius: 50%;
            width: 28px; height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
        }
        .cart-card-remove:hover { background: var(--error); color: #fff; border-color: var(--error); }

        /* Card body */
        .cart-card-body {
            padding: 1rem 1.1rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            flex: 1;
        }
        .cart-card-name {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
        }
        .cart-card-desc {
            font-size: 0.78rem;
            color: var(--text-light);
            line-height: 1.45;
        }
        .cart-card-prices {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.2rem;
        }
        .unit-price    { font-size: 0.78rem; color: var(--text-light); }
        .subtotal-price { font-size: 1.05rem; font-weight: 700; color: var(--primary-dark); }

        /* Qty controls */
        .qty-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.3rem;
        }
        .qty-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px; height: 30px;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--text);
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
        }
        .qty-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .qty-btn.disabled { opacity: 0.3; pointer-events: none; }
        .qty-num { font-size: 0.95rem; font-weight: 600; min-width: 26px; text-align: center; }

        /* Page layout */
        .cart-page-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 2.5rem;
            padding: 2.5rem 0;
            align-items: start;
        }
        .cart-left h3 {
            font-family: var(--font-display);
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 1.2rem;
        }
        @media (max-width: 900px) { .cart-page-layout  { grid-template-columns: 1fr; } }
        @media (max-width: 520px)  { .cart-cards-grid   { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">Style<span>Hub</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Shop</a></li>
            <li><a href="products.php?category=Men">Men</a></li>
            <li><a href="products.php?category=Women">Women</a></li>
            <li><a href="products.php?category=Kids">Kids</a></li>
        </ul>
        <div class="nav-right">
            <a href="cart.php" class="btn-icon active">🛒 Cart (<?= count($items) ?>)</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="admin.php" class="btn-outline">Admin</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-primary">Logout</a>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <h1>My Shopping Cart</h1>
    <p>Review your selected items before checkout</p>
</div>

<div class="container">

    <?php if (empty($items)): ?>
        <div class="empty-state">
            <div class="empty-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added anything yet!</p>
            <a href="products.php" class="btn-primary">Start Shopping</a>
        </div>

    <?php else: ?>
        <div class="cart-page-layout">

            <!-- LEFT: Product Cards -->
            <div class="cart-left">
                <h3>Items in Cart &nbsp;
                    <span style="font-size:0.88rem;color:var(--text-light);font-family:var(--font-body);font-weight:500;">
                        (<?= count($items) ?> item<?= count($items) > 1 ? 's' : '' ?>)
                    </span>
                </h3>

                <div class="cart-cards-grid">
                    <?php foreach ($items as $item): ?>

                    <!-- ==============================
                         ONE PRODUCT CARD STARTS HERE
                    ============================== -->
                    <div class="cart-product-card">

                        <!-- IMAGE (auto from Admin Panel) -->
                        <div class="cart-card-img">
                            <img
                                src="<?= htmlspecialchars(getImageSrc($item['image'], $item['name'])) ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>"
                                onerror="this.src='https://placehold.co/400x300/f5e8d5/c8975a?text=No+Image'"
                            >
                            <span class="cart-card-badge"><?= htmlspecialchars($item['category']) ?></span>
                            <a href="cart.php?action=remove&id=<?= $item['pid'] ?>"
                               class="cart-card-remove"
                               onclick="return confirm('Remove this item?')">✕</a>
                        </div>

                        <!-- NAME, DESCRIPTION, PRICE, QTY -->
                        <div class="cart-card-body">
                            <div class="cart-card-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="cart-card-desc"><?= htmlspecialchars(substr($item['description'], 0, 65)) ?>...</div>
                            <div class="cart-card-prices">
                                <span class="unit-price"><?= CURRENCY . number_format($item['price'], 2) ?> each</span>
                                <span class="subtotal-price"><?= CURRENCY . number_format($item['subtotal'], 2) ?></span>
                            </div>
                            <div class="qty-row">
                                <?php if ($item['quantity'] > 1): ?>
                                    <a href="cart.php?action=update&id=<?= $item['pid'] ?>&qty=<?= $item['quantity'] - 1 ?>" class="qty-btn">−</a>
                                <?php else: ?>
                                    <span class="qty-btn disabled">−</span>
                                <?php endif; ?>
                                <span class="qty-num"><?= $item['quantity'] ?></span>
                                <a href="cart.php?action=update&id=<?= $item['pid'] ?>&qty=<?= $item['quantity'] + 1 ?>" class="qty-btn">+</a>
                                <span style="font-size:0.73rem;color:var(--text-light);margin-left:2px;">qty</span>
                            </div>
                        </div>

                    </div>
                    <!-- ONE PRODUCT CARD ENDS HERE -->

                    <?php endforeach; ?>
                </div>

                <a href="products.php" class="btn-outline">← Continue Shopping</a>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <?php foreach ($items as $item): ?>
                    <div style="display:flex;justify-content:space-between;font-size:0.82rem;padding:0.4rem 0;border-bottom:1px solid var(--border);">
                        <span><?= htmlspecialchars($item['name']) ?> <span style="color:var(--text-light);">×<?= $item['quantity'] ?></span></span>
                        <strong><?= CURRENCY . number_format($item['subtotal'], 2) ?></strong>
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:1rem;">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span><?= CURRENCY . number_format($total, 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Delivery</span>
                        <span><?= $delivery == 0 ? '<span class="free">FREE</span>' : CURRENCY . '50.00' ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?= CURRENCY . number_format($grand_total, 2) ?></span>
                    </div>
                </div>
                <?php if ($total < 999): ?>
                    <p class="free-delivery-hint">Add <?= CURRENCY . number_format(999 - $total, 2) ?> more for FREE delivery!</p>
                <?php endif; ?>
                <a href="checkout.php" class="btn-primary full-width large" style="margin-top:1.2rem;display:block;text-align:center;">
                    Proceed to Checkout →
                </a>
            </div>

        </div>
    <?php endif; ?>
</div>

<div id="toast" class="toast hidden">✅ Cart updated!</div>

<!-- Footer -->
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
