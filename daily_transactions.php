<?php
include 'db_connection.php';

date_default_timezone_set('Africa/Nairobi');

$today = date('Y-m-d');

$sql = "
SELECT * FROM transactions
WHERE DATE(created_at) = '$today'
ORDER BY id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Today's Transactions</title>

<style>

body{
    font-family:'Segoe UI',sans-serif;
    background:#f5f5f5;
    margin:0;
    padding:20px;
}

h2{
    color:#d50000;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th, td{
    padding:12px;
    border:1px solid #ccc;
    text-align:center;
}

th{
    background:#d50000;
    color:white;
}

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    background:#d50000;
    color:white;
    padding:10px 18px;
    text-decoration:none;
    border-radius:6px;
}

</style>

</head>

<body>

<h2>Today's Transactions</h2>

<table>

<tr>
<th>ID</th>
<th>Network</th>
<th>Type</th>
<th>Amount</th>
<th>Date</th>
<th>Notes</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?= $row['id'] ?></td>
<td><?= $row['network'] ?></td>
<td><?= $row['type'] ?></td>
<td>Tsh <?= number_format($row['amount'],2) ?></td>
<td><?= $row['created_at'] ?></td>
<td><?= $row['notes'] ?></td>

</tr>

<?php endwhile; ?>

</table>
<a href="dashboard.php" class="back-btn">
    <br></br>

    
Back
</a>

</body>
</html>

<?php $conn->close(); ?>