<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $database = new Database();
    $db = $database->getConnection();

    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        throw new Exception('All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate username (alphanumeric only)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new Exception('Username can only contain letters, numbers, and underscores');
    }

    // Check if email or username exists
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        if ($existing['email'] === $email) {
            throw new Exception('Email already registered');
        }
        if ($existing['username'] === $username) {
            throw new Exception('Username already taken');
        }
    }

    // Insert new user
    $query = "INSERT INTO users (username, full_name, email, password_hash, status, created_at) VALUES (:username, :name, :email, :pass, 'active', NOW())";
    $stmt = $db->prepare($query);
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    if ($stmt->execute([
        ':username' => $username,
        ':name' => $full_name,
        ':email' => $email,
        ':pass' => $password_hash
    ])) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to create account');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
