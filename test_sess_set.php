<?php
session_set_cookie_params(0, '/');
session_start();
$_SESSION['test_var'] = 'Hello World';
$_SESSION['time'] = time();
session_write_close();
echo "Session set. ID: " . session_id();
?>
