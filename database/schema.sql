CREATE DATABASE IF NOT EXISTS `shop-database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `shop-database`;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(45) DEFAULT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    old_price DECIMAL(10,2) DEFAULT NULL,
    category VARCHAR(120) DEFAULT NULL,
    category_name VARCHAR(120) DEFAULT NULL,
    image TEXT,
    description TEXT,
    stock INT NOT NULL DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 5.00,
    reviews_count INT DEFAULT 0,
    badge VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(80) NOT NULL UNIQUE,
    user_id VARCHAR(100) DEFAULT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(45) DEFAULT NULL,
    shipping_address TEXT NOT NULL,
    city VARCHAR(120) DEFAULT NULL,
    payment_method VARCHAR(80) DEFAULT NULL,
    payment_status VARCHAR(80) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount DECIMAL(10,2) NOT NULL DEFAULT 0,
    shipping DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO users (full_name, email, phone, role, is_active, password_hash)
VALUES
  ('Alisha Store Admin', 'admin@alisha.com', '9828176055', 'admin', 1, '$2y$10$N4m9QYVAYAFSAa7zzlD3OeSHl3pWb1Vewx5HcDb7JXnR0l8i9yQwm'),
  ('Alisha Magar', 'punalisha100@gmail.com', '9828176055', 'user', 1, '$2y$10$9v0Moxjuz8d8yRsE1Iti7e9w6Gk8aT6b1fV9VfFjgN6LwMcSet0S6')
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  phone = VALUES(phone),
  role = VALUES(role),
  is_active = VALUES(is_active),
  password_hash = VALUES(password_hash);

SELECT 'Database ready.' AS status;
