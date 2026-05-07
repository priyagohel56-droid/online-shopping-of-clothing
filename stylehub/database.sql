
-- ============================================================
-- StyleHub - Clothing Store Database
-- Run this file in phpMyAdmin to set up your database
-- ============================================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS stylehub;
USE stylehub;

-- ============================================================
-- USERS TABLE - stores customer accounts
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,       -- stored as hashed password
    is_admin TINYINT(1) DEFAULT 0,        -- 1 = admin, 0 = regular user
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- ============================================================
-- PRODUCTS TABLE - stores clothing items
-- ============================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,        -- e.g. Men, Women, Kids
    image VARCHAR(255) DEFAULT 'placeholder.jpg',
    stock INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- CART TABLE - stores items users add to cart
-- ============================================================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- ORDERS TABLE - stores completed orders
-- ============================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status VARCHAR(30) DEFAULT 'Pending',  -- Pending, Processing, Delivered
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA - Admin account and demo products
-- ============================================================

-- Default Admin Account
-- Email: admin@stylehub.com | Password: admin123
INSERT INTO users (name, email, password, is_admin) VALUES
('Admin', 'admin@stylehub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Sample Products
INSERT INTO products (name, description, price, category, image, stock) VALUES
('Classic White Tee', 'Comfortable 100% cotton classic fit t-shirt. Perfect for everyday wear.', 599.00, 'Men', 'tshirt_white.jpg', 25),
('Slim Fit Jeans', 'Modern slim fit denim jeans with stretch fabric for comfort.', 1499.00, 'Men', 'jeans_blue.jpg', 15),
('Floral Summer Dress', 'Light and breezy floral print dress perfect for summer outings.', 1299.00, 'Women', 'dress_floral.jpg', 20),
('Casual Hoodie', 'Warm fleece hoodie with front pocket. Great for cool evenings.', 999.00, 'Unisex', 'hoodie_grey.jpg', 30),
('Kids Cartoon Tee', 'Fun and colorful cartoon printed t-shirt for children.', 399.00, 'Kids', 'kids_tee.jpg', 40),
('Women Blazer', 'Professional slim-fit blazer suitable for office and formal occasions.', 2199.00, 'Women', 'blazer_black.jpg', 10),
('Striped Polo Shirt', 'Classic striped polo shirt made with breathable fabric.', 799.00, 'Men', 'polo_stripe.jpg', 18),
('Girls Frock', 'Adorable cotton frock with colorful bow for little girls.', 599.00, 'Kids', 'girls_frock.jpg', 22);
