<?php
/**
 * User Authentication API
 * Handles ONLY user login - NO admin access
 */

header('Content-Type: application/json');
session_set_cookie_params(0, '/');
session_start();

require_once '../../config/database.php';

// Prevent admin access from this endpoint
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
    
    // Query ONLY from users table (NOT admins) - Fixed parameter binding
    $query = "SELECT id, username, email, password_hash, full_name, role, status 
              FROM users 
              WHERE (username = :username OR email = :email) 
              AND role = 'user'
              LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $username);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Log failed attempt
        logAuditTrail($db, 'user', 0, $username, 'login', 'login', null, null, 'failure', 
                     $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', 
                     json_encode(['reason' => 'Invalid credentials']));
        
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    // Check account status
    if ($user['status'] !== 'active') {
        logAuditTrail($db, 'user', $user['id'], $user['username'], 'login', 'login', null, null, 'blocked', 
                     $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', 
                     json_encode(['reason' => 'Account suspended']));
        
        echo json_encode(['success' => false, 'message' => 'Account is suspended. Contact administrator.']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        logAuditTrail($db, 'user', $user['id'], $user['username'], 'login', 'login', null, null, 'failure', 
                     $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '', 
                     json_encode(['reason' => 'Invalid password']));
        
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    // Create session
    $session_token = bin2hex(random_bytes(32));
    
    $query = "INSERT INTO user_sessions (user_type, user_id, session_token, ip_address, user_agent, expires_at) 
              VALUES ('user', :user_id, :token, :ip, :ua, DATE_ADD(NOW(), INTERVAL 8 HOUR))";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':user_id' => $user['id'],
        ':token' => $session_token,
        ':ip' => $_SERVER['REMOTE_ADDR'],
        ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Update last login
    $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $user['id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = 'user';
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['session_token'] = $session_token;
    
    // Log successful login
    logAuditTrail($db, 'user', $user['id'], $user['username'], 'User login successful', 'login', 
                 null, null, 'success', $_SERVER['REMOTE_ADDR'], 
                 $_SERVER['HTTP_USER_AGENT'] ?? '', json_encode(['session_token' => substr($session_token, 0, 8) . '...']));
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'email' => $user['email']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    // DEBUG MODE: Showing actual error
    echo json_encode([
        'success' => false, 
        'message' => 'Server Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
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
