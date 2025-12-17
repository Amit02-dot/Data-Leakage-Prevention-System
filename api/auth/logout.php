<?php
/**
 * Logout API
 * Handles both user and admin logout
 */

session_start();

require_once '../../includes/auth.php';

// Capture user type before destroying session
$user_type = $_SESSION['user_type'] ?? '';

// Perform logout (destroys session)
Auth::logout();

// Redirect based on previous user type
$redirect = ($user_type === 'admin') 
    ? '/DLPs/admin-login.php' 
    : '/DLPs/index.php';

header('Location: ' . $redirect);
exit;
