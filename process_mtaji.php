<?php

include 'db_connection.php';

error_reporting(E_ALL);
ini_set('display_errors',1);

$amount = $_POST['amount'];
$notes  = $_POST['notes'];

/* ADD CASH */
$sql1 = "
UPDATE accounts
SET balance = balance + $amount
WHERE network='CASH'
AND account_type='Cash_on_hand'
";

if(!$conn->query($sql1)){
    die("Cash Update Failed: " . $conn->error);
}

/* SAVE TRANSACTION */
$sql2 = "
INSERT INTO transactions
(type, network, amount, notes)
VALUES
('mtaji','CASH','$amount','$notes')
";

if(!$conn->query($sql2)){
    die("Transaction Failed: " . $conn->error);
}

/* RETURN */
header("Location: dashboard.php");
exit();

?>