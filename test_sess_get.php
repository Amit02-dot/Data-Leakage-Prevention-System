<?php
session_set_cookie_params(0, '/');
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Test Var: " . ($_SESSION['test_var'] ?? 'NOT SET') . "<br>";
echo "Time: " . ($_SESSION['time'] ?? 'NOT SET');
?>
