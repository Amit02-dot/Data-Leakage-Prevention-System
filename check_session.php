<?php
session_start();
header('Content-Type: text/plain');
echo "Session ID: " . session_id() . "\n";
echo "Session Vars:\n";
print_r($_SESSION);

require_once 'config/database.php';
try {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT NOW() as db_time");
    $db_time = $stmt->fetch(PDO::FETCH_ASSOC)['db_time'];
    echo "\nDB Time: " . $db_time . "\n";
    echo "PHP Time: " . date('Y-m-d H:i:s') . "\n";
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage();
}
?>
