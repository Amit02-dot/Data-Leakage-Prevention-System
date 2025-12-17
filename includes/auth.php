<?php
/**
 * Authentication Middleware
 * Validates user sessions and enforces RBAC
 */

session_set_cookie_params(0, '/');
session_start();

class Auth {
    /**
     * Check if user is authenticated
     */
    public static function requireUser() {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'user') {
            header('Location: /DLPs/index.php');
            exit;
        }
        
        // Validate session token
        if (!self::validateSession('user', $_SESSION['user_id'] ?? 0, $_SESSION['session_token'] ?? '')) {
            session_destroy();
            header('Location: /DLPs/index.php');
            exit;
        }
        
        return $_SESSION['user_id'];
    }
    
    /**
     * Check if admin is authenticated
     */
    public static function requireAdmin() {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: /DLPs/admin-login.php');
            exit;
        }
        
        // Validate session token
        if (!self::validateSession('admin', $_SESSION['admin_id'] ?? 0, $_SESSION['session_token'] ?? '')) {
            session_destroy();
            header('Location: /DLPs/admin-login.php');
            exit;
        }
        
        return $_SESSION['admin_id'];
    }
    
    /**
     * Validate session token from database
     */
    private static function validateSession($user_type, $user_id, $token) {
        if (empty($token) || empty($user_id)) {
            return false;
        }
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $db = $database->getConnection();
            
            $query = "SELECT id FROM user_sessions 
                     WHERE user_type = :type 
                     AND user_id = :uid 
                     AND session_token = :token 
                     AND expires_at > NOW() 
                     LIMIT 1";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':type' => $user_type,
                ':uid' => $user_id,
                ':token' => $token
            ]);
            
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get current user info
     */
    public static function getUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'type' => $_SESSION['user_type'] ?? ''
        ];
    }
    
    /**
     * Get current admin info
     */
    public static function getAdmin() {
        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['username'] ?? '',
            'full_name' => $_SESSION['full_name'] ?? '',
            'email' => $_SESSION['email'] ?? '',
            'role' => $_SESSION['admin_role'] ?? '',
            'type' => $_SESSION['user_type'] ?? ''
        ];
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        $token = $_SESSION['session_token'] ?? '';
        
        if (!empty($token)) {
            try {
                require_once __DIR__ . '/../config/database.php';
                $database = new Database();
                $db = $database->getConnection();
                
                $query = "DELETE FROM user_sessions WHERE session_token = :token";
                $stmt = $db->prepare($query);
                $stmt->execute([':token' => $token]);
            } catch (Exception $e) {
                // Silent fail
            }
        }
        
        // Unset all session keys
        $_SESSION = array();
        session_unset();
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Finally, destroy the session
        session_destroy();
    }
}
