-- Canonical full-project schema for fresh imports in XAMPP/phpMyAdmin.
-- This file matches the runtime PHP database configuration.
CREATE DATABASE IF NOT EXISTS cibo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cibo_db;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  role VARCHAR(50) NOT NULL DEFAULT 'Admin',
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS restaurants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  cuisine VARCHAR(255) NOT NULL,
  location VARCHAR(150) NOT NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 4.0,
  delivery_time VARCHAR(50) NOT NULL DEFAULT '25-30 mins',
  image_path VARCHAR(255) DEFAULT NULL,
  hero_image_path VARCHAR(255) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  offer_text VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS menu_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_restaurant_category_slug (restaurant_id, slug),
  CONSTRAINT fk_menu_categories_restaurant
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS menu_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  description TEXT DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  food_type ENUM('veg', 'nonveg', 'egg', 'none') NOT NULL DEFAULT 'none',
  image_path VARCHAR(255) DEFAULT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_restaurant_item_slug (restaurant_id, slug),
  CONSTRAINT fk_menu_items_restaurant
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_menu_items_category
    FOREIGN KEY (category_id) REFERENCES menu_categories(id)
    ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  label VARCHAR(50) DEFAULT NULL,
  full_address TEXT NOT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) NOT NULL,
  postal_code VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_addresses_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  restaurant_id INT UNSIGNED NOT NULL,
  address_id INT UNSIGNED DEFAULT NULL,
  order_number VARCHAR(50) NOT NULL UNIQUE,
  customer_name VARCHAR(100) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  delivery_address TEXT NOT NULL,
  payment_method ENUM('cod', 'upi', 'card') NOT NULL DEFAULT 'cod',
  payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
  order_status ENUM('pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
  subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  placed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  delivered_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_orders_address
    FOREIGN KEY (address_id) REFERENCES addresses(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_orders_restaurant
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
    ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  menu_item_id INT UNSIGNED DEFAULT NULL,
  item_name VARCHAR(150) NOT NULL,
  item_price DECIMAL(10,2) NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  line_total DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_menu_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    ON DELETE SET NULL
);

INSERT INTO admins (name, email, password_hash)
SELECT 'Admin User', 'admin@cibo.local', '$2y$10$qU64t2lW8jJfHkhGSK1BIe72YIkArvjESMOpZQQINRk.RLlJph52O'
WHERE NOT EXISTS (
  SELECT 1 FROM admins WHERE email = 'admin@cibo.local'
);
