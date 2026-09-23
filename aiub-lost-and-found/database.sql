-- ============================================================
-- AIUB Lost & Found - Database Schema
-- Import this file into phpMyAdmin / MySQL before running the site
-- ============================================================

CREATE DATABASE IF NOT EXISTS aiub_lost_found CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aiub_lost_found;

-- ----------------------------
-- Users
-- ----------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    student_id VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- Lost Items
-- ----------------------------
CREATE TABLE lost_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    color VARCHAR(50) DEFAULT NULL,
    brand VARCHAR(80) DEFAULT NULL,
    location VARCHAR(150) NOT NULL,
    date_lost DATE NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('open','matched','resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Found Items
-- ----------------------------
CREATE TABLE found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_name VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    color VARCHAR(50) DEFAULT NULL,
    brand VARCHAR(80) DEFAULT NULL,
    location VARCHAR(150) NOT NULL,
    date_found DATE NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('open','matched','resolved') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Matches (auto-generated when lost & found items look similar)
-- ----------------------------
CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lost_item_id INT NOT NULL,
    found_item_id INT NOT NULL,
    match_score INT NOT NULL,
    status ENUM('pending','confirmed','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lost_item_id) REFERENCES lost_items(id) ON DELETE CASCADE,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (lost_item_id, found_item_id)
) ENGINE=InnoDB;

-- ----------------------------
-- Notifications
-- ----------------------------
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
