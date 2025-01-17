
-- Create database
CREATE DATABASE IF NOT EXISTS OnlineStore;
USE OnlineStore;

-- Create accounts table
CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert sample account data
INSERT INTO accounts (username, password) VALUES
('implacida', '$2y$10$9r6LA8Yf2oHC6AQgPlhmAe350YXrZhLkVtw8miSF/wj1r3wWDcBDS');

-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    cover VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    discount_price DECIMAL(10, 2)
);

-- Insert sample product data
INSERT INTO products (title, description, cover, price, discount_price) VALUES
('Gaming Laptop', 'High-performance laptop suitable for gaming and multitasking.', 'https://picsum.photos/200/300', 1200.00, 999.99),
('Office Desktop', 'Reliable desktop for office use with efficient processing capabilities.', 'https://picsum.photos/210/300', 700.00, NULL),
('All-in-One PC', 'Space-saving design with an integrated monitor.', 'https://picsum.photos/220/300', 850.00, NULL);

-- Select to confirm data insertion
SELECT * FROM accounts;
SELECT * FROM products;


-- Ensure admin account exists
INSERT INTO accounts (username, password)
VALUES ('admin', '$2y$10$zUjxihMvLs/hWzVz9f7ckuMNPaI1sZBpB6DvmT.YftU3b7M8vXpyC'); 
-- Password is 'admin'