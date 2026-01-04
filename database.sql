-- Database Schema for Live CCTV Manager
-- Create database
CREATE DATABASE IF NOT EXISTS cctvlive CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cctvlive;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'viewer') DEFAULT 'viewer',
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cameras table for managing RTSP streams
CREATE TABLE IF NOT EXISTS cameras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    rtsp_url TEXT NOT NULL,
    stream_key VARCHAR(32) UNIQUE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_stream_key (stream_key),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- Password hash generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role, email) VALUES 
('admin', '$2y$10$zYMBdGQo1iJjyOpc0J1UTenkb5yI1AOlwPwTnQuURIIlR00EOIF1C', 'admin', 'admin@cctvlive.local');

-- Sample cameras for testing (optional)
-- Uncomment to add sample data
-- INSERT INTO cameras (name, location, rtsp_url, stream_key, status) VALUES 
-- ('Camera 1', 'Main Entrance', 'rtsp://example.com:554/stream1', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 'active'),
-- ('Camera 2', 'Parking Lot', 'rtsp://example.com:554/stream2', 'b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7', 'active'),
-- ('Camera 3', 'Back Office', 'rtsp://example.com:554/stream3', 'c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8', 'inactive');
