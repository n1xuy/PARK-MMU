-- Create database if not exists
CREATE DATABASE IF NOT EXISTS mmu_parking;

-- Use database
USE mmu_parking;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    user_type ENUM('student', 'staff', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Parking zones table
CREATE TABLE IF NOT EXISTS parking_zones (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(50) NOT NULL,
    status ENUM('EMPTY', 'HALF-FULL', 'FULL', 'STAFF') NOT NULL DEFAULT 'EMPTY',
    coordinates VARCHAR(100) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    zone_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    status ENUM('EMPTY', 'HALF-FULL', 'FULL') NOT NULL,
    report_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES parking_zones(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_by INT(11) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- System logs table
CREATE TABLE IF NOT EXISTS system_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    log_type VARCHAR(50) NOT NULL,
    log_title VARCHAR(100) NOT NULL,
    user_id INT(11),
    ip_address VARCHAR(50) NOT NULL,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, user_type) VALUES
('admin', '$2y$10$hK7wYt5uw9UFzAjFZ1V85eH7bXjOZM3/8YSiU4sS8XRzRUNg84zC2', 'Administrator', 'admin@mmu.edu.my', 'admin');

-- Insert some default parking zones with coordinates
INSERT INTO parking_zones (zone_name, status, coordinates) VALUES
('P1', 'EMPTY', '2.9290,101.7774'),
('P2', 'EMPTY', '2.9300,101.7784'),
('P3', 'STAFF', '2.9310,101.7794'),
('P4', 'EMPTY', '2.9320,101.7804'),
('P5', 'HALF-FULL', '2.9330,101.7814'),
('P6', 'EMPTY', '2.9340,101.7824'),
('P7', 'FULL', '2.9350,101.7834'),
('P8', 'EMPTY', '2.9360,101.7844'),
('P9', 'STAFF', '2.9370,101.7854'),
('P10', 'EMPTY', '2.9380,101.7864'),
('P11', 'HALF-FULL', '2.9390,101.7874'),
('P12', 'EMPTY', '2.9400,101.7884'),
('P13', 'FULL', '2.9410,101.7894'),
('P14', 'EMPTY', '2.9420,101.7904'),
('P15', 'STAFF', '2.9430,101.7914');

-- Insert a default announcement
INSERT INTO announcements (title, content, created_by) VALUES
('Welcome to MMU Parking Finder', 'This system helps you find available parking spaces in MMU. Report parking status to help others!', 1); 