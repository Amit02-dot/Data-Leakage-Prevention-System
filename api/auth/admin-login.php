<?php
/**
 * Admin Authentication API
 * Handles ONLY admin login - NO user access
 */

header('Content-Type: application/json');
session_set_cookie_params(0, '/');
session_start();

require_once '../../config/database.php';

// Prevent user access from this endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }
    
    // Query ONLY from admins table (NOT users)
    $query = "SELECT id, username, email, password_hash, full_name, role, status 
              FROM admins 
              WHERE (username = :username OR email = :email) 
              AND role IN ('admin', 'super_admin')
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $username);
    $stmt->execute();
    
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        // Log failed attempt
        logAuditTrail($db, 'admin', 0, $username, 'Admin login attempt failed', 'login', null, null, 'failure', 
                     $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', 
                     json_encode(['reason' => 'Invalid credentials', 'severity' => 'high']));
        
        echo json_encode(['success' => false, 'message' => 'Invalid admin credentials']);
        exit;
    }
    
    // Check account status
    if ($admin['status'] !== 'active') {
        logAuditTrail($db, 'admin', $admin['id'], $admin['username'], 'Admin login blocked', 'access_denied', 
                     null, null, 'blocked', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', 
                     json_encode(['reason' => 'Account suspended', 'severity' => 'critical']));
        
        echo json_encode(['success' => false, 'message' => 'Admin account is suspended.']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $admin['password_hash'])) {
        logAuditTrail($db, 'admin', $admin['id'], $admin['username'], 'Admin login failed', 'login', 
                     null, null, 'failure', $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', 
                     json_encode(['reason' => 'Invalid password', 'severity' => 'high']));
        
        echo json_encode(['success' => false, 'message' => 'Invalid admin credentials']);
        exit;
    }
    
    // Create admin session
    $session_token = bin2hex(random_bytes(32));
    
    $query = "INSERT INTO user_sessions (user_type, user_id, session_token, ip_address, user_agent, expires_at) 
              VALUES ('admin', :user_id, :token, :ip, :ua, DATE_ADD(NOW(), INTERVAL 4 HOUR))";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':user_id' => $admin['id'],
        ':token' => $session_token,
        ':ip' => $_SERVER['REMOTE_ADDR'],
        ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Update last login
    $query = "UPDATE admins SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $admin['id']]);
    
    // Set session variables
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['user_type'] = 'admin';
    $_SESSION['username'] = $admin['username'];
    $_SESSION['full_name'] = $admin['full_name'];
    $_SESSION['email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['session_token'] = $session_token;
    
    // Log successful admin login
    logAuditTrail($db, 'admin', $admin['id'], $admin['username'], 'Admin login successful', 'login', 
                 null, null, 'success', $_SERVER['REMOTE_ADDR'], 
                 $_SERVER['HTTP_USER_AGENT'] ?? '', 
                 json_encode(['role' => $admin['role'], 'session_token' => substr($session_token, 0, 8) . '...']));
    
    session_write_close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin authentication successful',
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'full_name' => $admin['full_name'],
            'email' => $admin['email'],
            'role' => $admin['role']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}

function logAuditTrail($db, $actor_type, $actor_id, $actor_name, $action, $action_type, 
                       $resource_type, $resource_id, $status, $ip, $ua, $details) {
    try {
        $query = "INSERT INTO audit_logs (actor_type, actor_id, actor_name, action, action_type, 
                  resource_type, resource_id, status, ip_address, user_agent, details) 
                  VALUES (:actor_type, :actor_id, :actor_name, :action, :action_type, 
                  :resource_type, :resource_id, :status, :ip, :ua, :details)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':actor_type' => $actor_type,
            ':actor_id' => $actor_id,
            ':actor_name' => $actor_name,
            ':action' => $action,
            ':action_type' => $action_type,
            ':resource_type' => $resource_type,
            ':resource_id' => $resource_id,
            ':status' => $status,
            ':ip' => $ip,
            ':ua' => $ua,
            ':details' => $details
        ]);
    } catch (Exception $e) {
        // Silent fail for audit logging
    }
}
