<?php
// ============================================================
// checkout.php - Checkout Page
// ============================================================

session_start();
require_once 'config.php';

// Only logged in users can checkout
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// --- Fetch cart items ---
$cart_query = mysqli_prepare($conn,
    "SELECT cart.quantity, products.name, products.price, products.id AS pid
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.user_id = ?"
);
mysqli_stmt_bind_param($cart_query, "i", $user_id);
mysqli_stmt_execute($cart_query);
$cart_result = mysqli_stmt_get_result($cart_query);

$items = [];
$total = 0;
while ($item = mysqli_fetch_assoc($cart_result)) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $items[] = $item;
}

// If cart is empty, redirect
if (empty($items) && empty($success)) {
    header("Location: cart.php");
    exit();
}

$delivery    = ($total >= 999) ? 0 : 50;
$grand_total = $total + $delivery;

// --- Handle Order Placement ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = trim($_POST['name']);
    $address = trim($_POST['address']);
    $phone   = trim($_POST['phone']);

    if (empty($name) || empty($address) || empty($phone)) {
        $error = "Please fill in all delivery details.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Please enter a valid 10-digit phone number.";
    } else {
        $order_stmt = mysqli_prepare($conn,
            "INSERT INTO orders (user_id, total_amount, name, address, phone) VALUES (?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($order_stmt, "idsss", $user_id, $grand_total, $name, $address, $phone);

        if (mysqli_stmt_execute($order_stmt)) {
            // Clear cart
            $clear = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
            mysqli_stmt_bind_param($clear, "i", $user_id);
            mysqli_stmt_execute($clear);

            $success = "🎉 Your order has been placed successfully! We'll deliver soon.";
            $items   = [];
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - StyleHub</title>
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
            <li><a href="products.php">Shop</a></li>
        </ul>
        <div class="nav-right">
            <a href="cart.php" class="btn-icon">🛒 Cart</a>
            <a href="logout.php" class="btn-primary">Logout</a>
        </div>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <h1>Checkout</h1>
    <p>Almost there! Enter your delivery details</p>
</div>

<div class="container">
    <?php if ($success): ?>
        <!-- Order Success -->
        <div class="order-success">
            <div class="success-icon">✅</div>
            <h2>Order Placed!</h2>
            <p><?= htmlspecialchars($success) ?></p>
            <a href="products.php" class="btn-primary large">Continue Shopping</a>
        </div>

    <?php else: ?>
        <div class="checkout-layout">

            <!-- Delivery Form -->
            <div class="checkout-form-section">
                <h3>Delivery Information</h3>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="checkout.php" class="checkout-form">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Your full name" required
                               value="<?= htmlspecialchars($_SESSION['user_name']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="10-digit mobile number" required
                               maxlength="10" pattern="[0-9]{10}">
                    </div>

                    <div class="form-group">
                        <label>Delivery Address</label>
                        <textarea name="address" rows="4" placeholder="House No., Street, City, State, PIN" required></textarea>
                    </div>

                    <div class="payment-note">
                        <h4>💳 Payment Method</h4>
                        <label class="radio-option">
                            <input type="radio" name="payment" value="cod" checked>
                            Cash on Delivery (COD)
                        </label>
                    </div>

                    <button type="submit" class="btn-primary full-width large">Place Order 🎉</button>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="cart-summary">
                <h3>Your Order</h3>
                <?php foreach ($items as $item): ?>
                    <div class="checkout-item">
                        <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span><?= CURRENCY . number_format($item['subtotal'], 2) ?></span>
                    </div>
                <?php endforeach; ?>

                <hr style="border-color:#eee;margin:1rem 0;">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?= CURRENCY . number_format($total, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery</span>
                    <span><?= $delivery == 0 ? '<span class="free">FREE</span>' : CURRENCY . number_format($delivery, 2) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Grand Total</span>
                    <span><?= CURRENCY . number_format($grand_total, 2) ?></span>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; 2025 StyleHub</p>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
