<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$username = 'admin';
$password = 'Admin@123';

$stmt = $db->prepare("SELECT * FROM admins WHERE username = :u");
$stmt->execute([':u' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Admin user not found.\n";
} else {
    echo "Admin user found: " . $user['username'] . "\n";
    echo "Hash: " . $user['password_hash'] . "\n";
    if (password_verify($password, $user['password_hash'])) {
        echo "Password verification: SUCCESS\n";
    } else {
        echo "Password verification: FAILED\n";
    }
}
?>
