<?php
// ============================================================
// config.php - Database Connection File
// This file connects your website to the MySQL database.
// Include this file at the top of any page that needs database.
// ============================================================

// --- Database Settings (change these if needed) ---
define('DB_HOST', 'localhost');       // Usually 'localhost' for XAMPP
define('DB_USER', 'root');            // Default XAMPP username
define('DB_PASS', '');                // Default XAMPP password (empty)
define('DB_NAME', 'stylehub');        // Your database name

// --- Create Connection ---
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Check if connection worked ---
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// --- Set character encoding ---
mysqli_set_charset($conn, "utf8");

// --- Site Settings ---
define('SITE_NAME', 'StyleHub');
define('CURRENCY', '₹');
?>
