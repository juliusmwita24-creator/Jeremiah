<?php
include 'db_connection.php';

$network = $_GET['network'] ?? null;
$type = $_GET['type'] ?? null;

if (!$network || !$type) {
    die("Network or account type not specified.");
}

$stmt = $conn->prepare("SELECT * FROM transactions WHERE network = ? AND (from_account = ? OR to_account = ?) ORDER BY date DESC LIMIT 50");
$stmt->bind_param("sss", $network, $type, $type);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($network) ?> - <?= htmlspecialchars($type) ?> Transactions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #fdfdfd;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        header {
            background-color: #d50000;
            color: white;
            padding: 18px 20px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(213,0,0,0.4);
            margin-bottom: 25px;
        }
        .container {
            max-width: 960px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(213, 0, 0, 0.15);
        }
        h2 {
            color: #d50000;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.3rem;
            border-bottom: 3px solid #d50000;
            padding-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #d50000;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            color: #d50000;
            font-weight: 600;
            font-size: 1rem;
        }
        .no-transactions {
            font-size: 1.1rem;
            color: #555;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                display: none;
            }
            td {
                padding: 10px;
                text-align: right;
                position: relative;
                border-bottom: 1px solid #ccc;
            }
            td::before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                font-weight: bold;
                color: #555;
                text-align: left;
            }
        }
    </style>
</head>
<body>

<header>
    <?= htmlspecialchars($network) ?> - <?= htmlspecialchars($type) ?> Transactions
</header>

<div class="container">
    <a class="back-link" href="dashboard.php">&larr; Back to Dashboard</a>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Date"><?= htmlspecialchars($row['date']) ?></td>
                        <td data-label="Type"><?= htmlspecialchars($row['type']) ?></td>
                        <td data-label="From"><?= htmlspecialchars($row['from_account']) ?></td>
                        <td data-label="To"><?= htmlspecialchars($row['to_account']) ?></td>
                        <td data-label="Amount">Tsh <?= number_format($row['amount'], 2) ?></td>
                        <td data-label="Notes"><?= htmlspecialchars($row['notes']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-transactions">No transactions found for <?= htmlspecialchars($type) ?>.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
