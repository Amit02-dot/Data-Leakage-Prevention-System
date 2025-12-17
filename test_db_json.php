<?php
require_once 'config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo json_encode(['success' => true, 'message' => 'Database connected successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
