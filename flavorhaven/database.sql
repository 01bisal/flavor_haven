CREATE DATABASE IF NOT EXISTS flavorhaven_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE flavorhaven_db;

-- =====================
-- USERS
-- =====================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','member','normal') DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- MENU CATEGORIES
-- =====================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL
);

INSERT INTO categories (name, slug) VALUES
('Appetizers', 'appetizers'),
('Main Courses', 'main'),
('Pasta', 'pasta'),
('Pizza', 'pizza'),
('Desserts', 'desserts'),
('Drinks', 'drinks');

-- =====================
-- MENU ITEMS
-- =====================
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT NOT NULL,
    image_path VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================
-- ORDERS
-- =====================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','preparing','delivered','cancelled') DEFAULT 'pending',
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================
-- ORDER ITEMS
-- =====================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- =====================
-- CONTACT MESSAGES
-- =====================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    preferred_contact TINYINT(1) DEFAULT 0,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- SAMPLE MENU ITEMS 
-- =====================
INSERT INTO menu_items (name, description, price, category_id, image_path) VALUES
('Bruschetta', 'Toasted bread topped with fresh tomatoes, garlic, basil, and balsamic glaze', 12.99, 1, 'images/bruschetta.jpg'),
('Crispy Calamari', 'Lightly battered and fried calamari served with marinara sauce', 14.99, 1, 'images/calamari.jpg'),
('Grilled Salmon', 'Fresh Atlantic salmon grilled to perfection with lemon butter sauce', 28.99, 2, 'images/salmon.jpg'),
('Ribeye Steak', 'Prime ribeye steak with garlic mashed potatoes', 34.99, 2, 'images/ribeye.jpg'),
('Spaghetti Carbonara', 'Classic Roman pasta with pancetta, egg yolk, and pecorino cheese', 22.99, 3, 'images/Spaghetti.jpg'),
('Fettuccine Alfredo', 'Creamy fettuccine with grilled chicken and fresh broccoli', 20.99, 3, 'images/alfredo.jpg'),
('Margherita Pizza', 'Wood-fired pizza with tomato sauce, fresh mozzarella, and basil', 18.99, 4, 'images/margarita.jpg'),
('Pepperoni Pizza', 'Classic pepperoni with mozzarella and Italian herbs', 20.99, 4, 'images/pepporoni.jpg'),
('Tiramisu', 'Classic Italian dessert with coffee-soaked ladyfingers and mascarpone', 10.99, 5, 'images/tiramisu.jpg'),
('Chocolate Lava Cake', 'Warm chocolate cake with molten center and vanilla ice cream', 11.99, 5, 'images/lavacake.jpg'),
('Berry Smoothie', 'Fresh berries blended with Greek yogurt and honey', 8.99, 6, 'images/berriessmothie.jpg'),
('Fresh Brewed Coffee', 'Rich and aromatic coffee from premium beans', 4.99, 6, 'images/coffee.jpg');

-- REVIEWS TABLE
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_item (user_id, menu_item_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);