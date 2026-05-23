-- Cibo canonical schema
-- Fresh-import schema for the customer site, admin panel, and API layer.
-- Import this file into MySQL/MariaDB and keep `includes/db.php` pointed at `cibo_db`.

CREATE DATABASE IF NOT EXISTS cibo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cibo_db;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS receipts;
DROP TABLE IF EXISTS payment_intents;
DROP TABLE IF EXISTS user_otp_challenges;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS restaurants;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_verified TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_users_email (email),
  KEY idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL DEFAULT 'Admin User',
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_admin_users_email (email),
  KEY idx_admin_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE addresses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  label VARCHAR(50) NOT NULL DEFAULT 'Home',
  recipient_name VARCHAR(120) DEFAULT NULL,
  recipient_phone VARCHAR(20) DEFAULT NULL,
  address_line VARCHAR(255) NOT NULL,
  landmark VARCHAR(120) DEFAULT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) DEFAULT NULL,
  pincode VARCHAR(20) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_addresses_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  KEY idx_addresses_user (user_id),
  KEY idx_addresses_default (user_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE restaurants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  hero_image VARCHAR(255) DEFAULT NULL,
  category VARCHAR(120) NOT NULL,
  cuisine VARCHAR(255) DEFAULT NULL,
  location VARCHAR(150) NOT NULL,
  address VARCHAR(255) DEFAULT NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 4.0,
  delivery_time VARCHAR(50) NOT NULL DEFAULT '25-30 mins',
  offer_text VARCHAR(255) DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_restaurants_slug (slug),
  KEY idx_restaurants_active_location (is_active, location),
  KEY idx_restaurants_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  food_type ENUM('veg', 'nonveg', 'egg', 'none') NOT NULL DEFAULT 'none',
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_menu_items_restaurant
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
    ON DELETE CASCADE,
  UNIQUE KEY uk_menu_items_restaurant_slug (restaurant_id, slug),
  KEY idx_menu_items_restaurant_available (restaurant_id, is_available)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED DEFAULT NULL,
  restaurant_id INT UNSIGNED NOT NULL,
  address_id INT UNSIGNED DEFAULT NULL,
  order_number VARCHAR(50) NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  customer_phone VARCHAR(20) NOT NULL,
  delivery_address VARCHAR(255) NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  final_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  payment_method ENUM('COD', 'UPI', 'CARD') NOT NULL DEFAULT 'COD',
  payment_status ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
  gateway_name VARCHAR(40) DEFAULT NULL,
  gateway_order_id VARCHAR(80) DEFAULT NULL,
  gateway_payment_id VARCHAR(80) DEFAULT NULL,
  payment_verified_at DATETIME DEFAULT NULL,
  order_status ENUM('placed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'placed',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  placed_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  delivered_at TIMESTAMP NULL DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_orders_restaurant
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
    ON DELETE RESTRICT,
  CONSTRAINT fk_orders_address
    FOREIGN KEY (address_id) REFERENCES addresses(id)
    ON DELETE SET NULL,
  UNIQUE KEY uk_orders_order_number (order_number),
  KEY idx_orders_user_created (user_id, created_at),
  KEY idx_orders_restaurant_created (restaurant_id, created_at),
  KEY idx_orders_status_created (order_status, created_at),
  KEY idx_orders_payment_status (payment_status),
  KEY idx_orders_gateway_order (gateway_order_id),
  KEY idx_orders_gateway_payment (gateway_payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payment_intents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  intent_token VARCHAR(80) NOT NULL,
  user_id INT UNSIGNED DEFAULT NULL,
  gateway_name VARCHAR(40) NOT NULL DEFAULT 'razorpay',
  payment_method VARCHAR(20) NOT NULL,
  gateway_order_id VARCHAR(80) NOT NULL,
  gateway_payment_id VARCHAR(80) DEFAULT NULL,
  amount_paise INT UNSIGNED NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'INR',
  status VARCHAR(20) NOT NULL DEFAULT 'created',
  customer_name VARCHAR(120) NOT NULL DEFAULT '',
  customer_phone VARCHAR(20) NOT NULL DEFAULT '',
  customer_email VARCHAR(190) NOT NULL DEFAULT '',
  checkout_payload_json LONGTEXT NOT NULL,
  summary_json LONGTEXT NOT NULL,
  cibo_order_number VARCHAR(50) DEFAULT NULL,
  payment_verified_at DATETIME DEFAULT NULL,
  last_error VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_payment_intents_token (intent_token),
  UNIQUE KEY uk_payment_intents_gateway_order (gateway_order_id),
  KEY idx_payment_intents_status_created (status, created_at),
  KEY idx_payment_intents_gateway_payment (gateway_payment_id),
  KEY idx_payment_intents_cibo_order (cibo_order_number),
  CONSTRAINT fk_payment_intents_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  menu_item_id INT UNSIGNED DEFAULT NULL,
  item_name VARCHAR(150) NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_menu_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    ON DELETE SET NULL,
  KEY idx_order_items_order (order_id),
  KEY idx_order_items_menu_item (menu_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE receipts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  receipt_number VARCHAR(60) NOT NULL,
  generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_receipts_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  UNIQUE KEY uk_receipts_order_id (order_id),
  UNIQUE KEY uk_receipts_receipt_number (receipt_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_otp_challenges (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  purpose ENUM('signup', 'login', 'password_reset') NOT NULL,
  otp_code_hash VARCHAR(255) NOT NULL,
  channel ENUM('sms', 'email') NOT NULL DEFAULT 'email',
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME DEFAULT NULL,
  attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_otp_challenges_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,
  KEY idx_user_otp_challenges_lookup (user_id, purpose, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_users (name, email, password_hash, role)
SELECT
  'Admin User',
  'admin@cibo.local',
  '$2y$10$Iro4jtKhhhKTyl5V0C1i/etzTseu2xDbni6bTmqy9zbthjeVd3Tye',
  'super_admin'
WHERE NOT EXISTS (
  SELECT 1 FROM admin_users WHERE email = 'admin@cibo.local'
);
