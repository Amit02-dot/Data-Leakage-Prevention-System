-- Enterprise DLP System Database Schema
-- Production-Ready with Security & Audit Features

CREATE DATABASE IF NOT EXISTS dlps_enterprise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dlps_enterprise;

-- Users Table (End Users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('user') DEFAULT 'user',
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admins Table (Security Administrators - SEPARATE)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    status ENUM('active', 'suspended') DEFAULT 'active',
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DLP Policies Table
CREATE TABLE IF NOT EXISTS dlp_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_name VARCHAR(255) NOT NULL,
    description TEXT,
    data_type ENUM('pii', 'financial', 'confidential', 'health', 'custom') NOT NULL,
    condition_type ENUM('keyword', 'regex', 'pattern', 'ml') DEFAULT 'keyword',
    condition_value TEXT NOT NULL,
    action ENUM('block', 'warn', 'quarantine', 'encrypt', 'alert') DEFAULT 'block',
    severity ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_data_type (data_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File Scans Table
CREATE TABLE IF NOT EXISTS file_scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size BIGINT NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    scan_status ENUM('pending', 'scanning', 'completed', 'failed') DEFAULT 'pending',
    risk_level ENUM('safe', 'low', 'medium', 'high', 'critical', 'blocked') DEFAULT 'safe',
    policy_triggered INT NULL,
    scan_result JSON,
    sensitive_data_found JSON,
    scan_duration_ms INT,
    scanned_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (policy_triggered) REFERENCES dlp_policies(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_risk_level (risk_level),
    INDEX idx_scan_status (scan_status),
    INDEX idx_scanned_at (scanned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Trail Table (Immutable Logs)
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    actor_type ENUM('user', 'admin', 'system') NOT NULL,
    actor_id INT NOT NULL,
    actor_name VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    action_type ENUM('login', 'logout', 'upload', 'scan', 'policy_create', 'policy_update', 'policy_delete', 'access_denied', 'file_blocked', 'config_change') NOT NULL,
    resource_type VARCHAR(100),
    resource_id INT,
    status ENUM('success', 'failure', 'blocked', 'warning') NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor (actor_type, actor_id),
    INDEX idx_action_type (action_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scanning Rules Table
CREATE TABLE IF NOT EXISTS scanning_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_name VARCHAR(255) NOT NULL,
    rule_type ENUM('keyword', 'regex', 'pattern', 'file_type') NOT NULL,
    rule_value TEXT NOT NULL,
    description TEXT,
    enabled BOOLEAN DEFAULT TRUE,
    priority INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_enabled (enabled),
    INDEX idx_rule_type (rule_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Sessions Table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('user', 'admin') NOT NULL,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin (Password: Admin@123)
INSERT INTO admins (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@dlps.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Insert Default User (Password: User@123)
INSERT INTO users (username, email, password_hash, full_name) VALUES
('testuser', 'user@dlps.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test User');

-- Insert Default DLP Policies
INSERT INTO dlp_policies (policy_name, description, data_type, condition_type, condition_value, action, severity, created_by) VALUES
('SSN Detection', 'Detects US Social Security Numbers', 'pii', 'regex', '\\b\\d{3}-\\d{2}-\\d{4}\\b', 'block', 'critical', 1),
('Credit Card Detection', 'Detects credit card numbers', 'financial', 'regex', '\\b\\d{4}[\\s-]?\\d{4}[\\s-]?\\d{4}[\\s-]?\\d{4}\\b', 'block', 'critical', 1),
('Email Detection', 'Detects email addresses', 'pii', 'regex', '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}', 'warn', 'medium', 1),
('Confidential Keyword', 'Detects confidential keywords', 'confidential', 'keyword', 'confidential,secret,internal,proprietary', 'warn', 'high', 1),
('Phone Number Detection', 'Detects phone numbers', 'pii', 'regex', '\\b\\d{3}[-.]?\\d{3}[-.]?\\d{4}\\b', 'warn', 'medium', 1);

-- Insert Default Scanning Rules
INSERT INTO scanning_rules (rule_name, rule_type, rule_value, description, enabled, priority) VALUES
('SSN Pattern', 'regex', '\\b\\d{3}-\\d{2}-\\d{4}\\b', 'Social Security Number pattern', TRUE, 10),
('Credit Card Pattern', 'regex', '\\b\\d{4}[\\s-]?\\d{4}[\\s-]?\\d{4}[\\s-]?\\d{4}\\b', 'Credit card number pattern', TRUE, 10),
('API Key Pattern', 'regex', '(api[_-]?key|apikey)[\\s:=]+[\'"]?([a-zA-Z0-9_\\-]{32,})[\'"]?', 'API key detection', TRUE, 9),
('Password Keyword', 'keyword', 'password,passwd,pwd', 'Password keyword detection', TRUE, 8),
('Confidential Keyword', 'keyword', 'confidential,secret,classified,restricted', 'Confidential content detection', TRUE, 7);

-- Create Views for Dashboard Analytics

-- User Dashboard View
CREATE OR REPLACE VIEW user_dashboard_stats AS
SELECT 
    u.id as user_id,
    COUNT(DISTINCT fs.id) as total_scans,
    COUNT(DISTINCT CASE WHEN fs.scan_status = 'completed' THEN fs.id END) as completed_scans,
    COUNT(DISTINCT CASE WHEN fs.risk_level = 'blocked' THEN fs.id END) as blocked_files,
    COUNT(DISTINCT CASE WHEN fs.risk_level IN ('high', 'critical') THEN fs.id END) as risk_alerts,
    COUNT(DISTINCT CASE WHEN fs.risk_level = 'safe' THEN fs.id END) as safe_files
FROM users u
LEFT JOIN file_scans fs ON u.id = fs.user_id
GROUP BY u.id;

-- Admin Dashboard View
CREATE OR REPLACE VIEW admin_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE status = 'active') as total_users,
    (SELECT COUNT(*) FROM file_scans) as total_scans,
    (SELECT COUNT(*) FROM file_scans WHERE risk_level IN ('high', 'critical', 'blocked')) as policy_violations,
    (SELECT COUNT(*) FROM file_scans WHERE risk_level IN ('high', 'critical') AND scanned_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as high_risk_alerts,
    (SELECT COUNT(*) FROM dlp_policies WHERE status = 'active') as active_policies;
