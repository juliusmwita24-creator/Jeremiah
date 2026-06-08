<?php
include 'db_connection.php';

date_default_timezone_set('Africa/Nairobi');
$type = $_POST['type'] ?? '';
$network = $_POST['network'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$notes = $_POST['notes'] ?? '';
$actual_lipa_amount = isset($_POST['actual_lipa_amount']) ? floatval($_POST['actual_lipa_amount']) : null;
$created_at = date('Y-m-d H:i:s');

if ($amount <= 0 || !$type){
    echo "Tafadhali jaza taarifa zote kwa usahihi.";
    exit;
}

function get_balance($conn, $network, $account_type) {
    $stmt = $conn->prepare("SELECT balance FROM accounts WHERE network = ? AND account_type = ?");
    $stmt->bind_param("ss", $network, $account_type);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc()['balance'] ?? 0;
}

function update_balance($conn, $network, $account_type, $amount_change) {
    $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE network = ? AND account_type = ?");
    $stmt->bind_param("dss", $amount_change, $network, $account_type); 
    return $stmt->execute();
}

$conn->begin_transaction();

try {
    if ($type === 'withdrawal') {
        $from_account = 'Cash_on_hand';
        $to_account = 'Till';

        if ($amount > get_balance($conn, 'CASH', 'Cash_on_hand')) {
            throw new Exception("Cash haitoshi kulipa mteja.");
        }

        $stmt = $conn->prepare("INSERT INTO transactions (network, type, from_account, to_account, amount, created_at, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $network, $type, $from_account, $to_account, $amount, $created_at, $notes);
        $stmt->execute();

        update_balance($conn, 'CASH', 'Cash_on_hand', -$amount);
        update_balance($conn, $network, 'Till', $amount);
    }

    elseif ($type === 'deposit') {
        $from_account = 'Till';
        $to_account = 'Cash_on_hand';

        if ($amount > get_balance($conn, $network, 'Till')) {
            throw new Exception("Float ya kutuma haitoshi.");
        }

        $stmt = $conn->prepare("INSERT INTO transactions (network, type, from_account, to_account, amount, created_at, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $network, $type, $from_account, $to_account, $amount, $created_at, $notes);
        $stmt->execute();

        update_balance($conn, $network, 'Till', -$amount);
        update_balance($conn, 'CASH', 'Cash_on_hand', $amount);
    }

    elseif ($type === 'lipa_cashout') {
        $from_account = 'Cash_on_hand';
        $to_account = 'Lipa';

        if ($actual_lipa_amount === null || $actual_lipa_amount < $amount) {
            throw new Exception("Kiasi cha float lazima kiwe sawa au kikubwa kuliko kilicholipwa cash.");
        }
        if ($amount > get_balance($conn, 'CASH', 'Cash_on_hand')) {
            throw new Exception("Cash haitoshi kwa malipo ya LIPA.");
        }

        $stmt = $conn->prepare("INSERT INTO transactions 
            (network, type, from_account, to_account, amount, actual_lipa_amount, created_at, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssddss", $network, $type, $from_account, $to_account, $amount, $actual_lipa_amount, $created_at, $notes);
        $stmt->execute();

        update_balance($conn, 'CASH', 'Cash_on_hand', -$amount);
        update_balance($conn, $network, 'Lipa', $actual_lipa_amount);
    }

    elseif ($type === 'transfer_lipa_to_till') {
        $from_account = 'Lipa';
        $to_account = 'Till';

        if ($amount > get_balance($conn, $network, 'Lipa')) {
            throw new Exception("Float ya LIPA haitoshi.");
        }

        $stmt = $conn->prepare("INSERT INTO transactions (network, type, from_account, to_account, amount, created_at, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $network, $type, $from_account, $to_account, $amount, $created_at, $notes);
        $stmt->execute();

        update_balance($conn, $network, 'Lipa', -$amount);
        update_balance($conn, $network, 'Till', $amount);
    }

    elseif ($type === 'convert_cash_to_float') {
        $from_account = 'Cash_on_hand';
        $to_account = 'Till';

        if ($amount > get_balance($conn, 'CASH', 'Cash_on_hand')) {
            throw new Exception("Cash haitoshi kubadili kuwa float.");
        }

        $stmt = $conn->prepare("INSERT INTO transactions (network, type, from_account, to_account, amount, created_at, notes) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $network, $type, $from_account, $to_account, $amount, $created_at, $notes);
        $stmt->execute();

        update_balance($conn, 'CASH', 'Cash_on_hand', -$amount);
        update_balance($conn, $network, 'Till', $amount);
    }
    elseif ($type === 'float_transfer') {

    $from_network = $_POST['from_network'] ?? '';
    $to_network = $_POST['to_network'] ?? '';

    $from_account = 'Till';
    $to_account = 'Till';

    if (!$from_network || !$to_network) {
        throw new Exception("Chagua mitandao yote.");
    }

    if ($from_network === $to_network) {
        throw new Exception("Huwezi kutuma float kwenye mtandao huohuo.");
    }

    if ($amount > get_balance($conn, $from_network, 'Till')) {
        throw new Exception("Float haitoshi kwenye mtandao wa kutoa.");
    }

    $stmt = $conn->prepare("
        INSERT INTO transactions 
        (network, type, from_account, to_account, amount, created_at, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $network_name = $from_network . ' TO ' . $to_network;
    $stmt->bind_param("ssssdss",$network_name,$type,$from_account,$to_account,$amount,$created_at,);
     $stmt->execute();
    update_balance($conn, $from_network, 'Till', -$amount);
    update_balance($conn, $to_network, 'Till', $amount);
}
elseif ($type === 'convert_lipa_to_cash') {

    $from_account = 'Lipa';
    $to_account = 'Cash_on_hand';

    if ($amount > get_balance($conn, $network, 'Lipa')) {
        throw new Exception("Salio la LIPA halitoshi.");
    }

    $stmt = $conn->prepare("
        INSERT INTO transactions
        (network, type, from_account, to_account, amount, created_at, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssssss",
        $network,
        $type,
        $from_account,
        $to_account,
        $amount,
        $created_at,
        $notes
    );

    $stmt->execute();

    // Punguza LIPA
    update_balance($conn, $network, 'Lipa', -$amount);

    // Ongeza Cash
    update_balance($conn, 'CASH', 'Cash_on_hand', $amount);
}

    else {
        throw new Exception("Aina ya muamala haijulikani.");
    }
    $conn->commit();
    header("Location: dashboard.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "Hitilafu: " . $e->getMessage();
}
$conn->close();
?>
