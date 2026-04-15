-- Drop existing tables if needed
DROP TABLE IF EXISTS transations;
DROP TABLE IF EXISTS offers;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(100), 
    paytm_number VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    total_earned DECIMAL(10,2) DEFAULT 0.00,
    total_withdrawn DECIMAL(10,2) DEFAULT 0.00,
    referral_code VARCHAR(10) UNIQUE,
    reffered_by INT,
    is_active BOOLEAN DEFAULT TRUE,
    device_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mobile (mobile),
    INDEX idx_referral (referral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Transaction Table
CREATE TABLE transations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('earning', 'withdrawal', 'referral') NOT NULL,
    description VARCHAR(255),
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    reference_id VARCHAR(100),
    offer_id VARCHAR(50),
    payment_method VARCHAR(50),
    transation_data TEXT, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
    )ENGINE=InnoDB DEFAULT CHARSET=urf8mb4;

-- Offers Table
CREATE TABLE offers(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reward DECIMAL(10,2) NOT NULL, 
    image_url VARCHAR(500),
    action_url VARCHAR(500),
    package_name VARCHAR(255),
    category VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    priority INT DEFAULT 0, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_category (category)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample offers
-- INSERT INTO offers (title, description, reward, image_url, action_url, package_name, category) VALUES
-- ('Amazon Shopping', 'Install Amazon app and sign up', 15.00, 'https://example.com/amazon.png', 'https://play.google.com/store/apps/details?id=com.amazon.shop', 'com.amazon.shop', 'Shopping'),
-- ('Flipkart', 'Install Flipkart and browse products', 20.00, 'https://example.com/flipkart.png', 'https://play.google.com/store/apps/details?id=com.flipkart.android', 'com.flipkart.android', 'Shopping'),
-- ('Paytm First Game', 'Install Paytm First Game and play', 10.00, 'https://example.com/paytm.png', 'https://play.google.com/store/apps/details?id=net.one97.paytm', 'net.one97.paytm', 'Gaming'),
-- ('Meesho', 'install Meesho and register', 12.00, 'https://example.com/meesho.png', 'https://play.google.com/store/apps/details?id=com.meesho.supply', 'com.meesho.supply', 'Shopping');
