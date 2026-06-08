<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connection.php';
$network      = $_POST['network'];
$account_type = $_POST['account_type'];
$amount       = $_POST['amount'];
/* REMOVE CASH */
$sql1 = "UPDATE accounts SET balance = balance - $amount WHERE network='CASH'AND account_type='Cash_on_hand'";
$conn->query($sql1);
/* ADD FLOAT */
$sql2 = "UPDATE accounts SET balance = balance + $amount WHERE network='$network'AND account_type='$account_type'";
$conn->query($sql2);
/* SAVE TRANSACTION */
$sql3 = "INSERT INTO transactions(type, network, amount)VALUES('float_distribution','$network','$amount')";
$conn->query($sql3);
header("Location: dashboard.php");
?>