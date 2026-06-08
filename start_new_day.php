<?php
session_start();

$_SESSION['day_start_time'] = date('Y-m-d H:i:s');

include 'db_connection.php';

/* RESET BALANCES */

$conn->query("UPDATE accounts SET balance = 0");

header("Location: dashboard.php");
exit;
?>