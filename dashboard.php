<?php
include 'db_connection.php';
date_default_timezone_set('Africa/Nairobi');
/* =========================
   FETCH ACCOUNT BALANCES
========================= */
$sql = "SELECT network, account_type, balance FROM accounts";
$result = $conn->query($sql);
$balances = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $balances[$row['network']][$row['account_type']] = $row['balance'];
    }
}

/* =========================
   FETCH LIPA PROFIT
========================= */
session_start();
$start_time = $_SESSION['day_start_time']?? date('Y-m-d 01:00:00');
$sql_profit = "SELECT SUM(COALESCE(actual_lipa_amount,0)- COALESCE(amount,0)) AS total_profit FROM transactions WHERE type='lipa_cashout'AND created_at >= '$start_time'";
$res_profit = $conn->query($sql_profit);
$profit = 0;
if ($res_profit && $res_profit->num_rows > 0) {
    $row = $res_profit->fetch_assoc();
    $profit = $row['total_profit'] ?? 0;
}
/* =========================
   CALCULATE MTAJI
========================= */
$Mtaji =
    ($balances['CASH']['Cash_on_hand'] ?? 0) +
    ($balances['Airtel']['Till'] ?? 0) +
    ($balances['Yas']['Till'] ?? 0) +
    ($balances['Vodacom']['Till'] ?? 0) +
    ($balances['Halotel']['Till'] ?? 0) +
    ($balances['Airtel']['Lipa'] ?? 0) +
    ($balances['Yas']['Lipa'] ?? 0) +
    ($balances['Vodacom']['Lipa'] ?? 0) +
    ($balances['Halotel']['Lipa'] ?? 0);

/* =========================
   BANKS
========================= */
$banks = [
    "NMB",
    "CRDB",
    "NBC",
];

/* =========================
   NETWORKS
========================= */
$networks = [
    "Airtel",
    "Yas",
    "Vodacom",
    "Halotel"
];

/* =========================
   GRAPH DATA
========================= */
$start_time =$_SESSION['day_start_time']?? date('Y-m-d 01:00:00');
$graphQuery = "SELECT DAYNAME(created_at) AS day_name,network, COUNT(*) AS total FROM transactions WHERE created_at >= '$start_time'GROUP BY DAYNAME(created_at), network ORDER BY MIN(created_at) ASC";
$graphResult = $conn->query($graphQuery);
$days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
$networkData = ["Airtel"=>[0, 0, 0, 0, 0, 0, 0],"Yas"=>[0, 0, 0, 0, 0, 0, 0],"Vodacom" =>[0, 0, 0, 0, 0, 0, 0],"Halotel" => [0, 0, 0, 0, 0, 0, 0]];
$dayMap = ["Monday"=>0,"Tuesday"=>1,"Wednesday"=>2,"Thursday"=>3,"Friday"=>4,"Saturday"=>5,"Sunday"=>6];
if ($graphResult && $graphResult->num_rows > 0) {
    while ($g = $graphResult->fetch_assoc()) {
        $dayIndex = $dayMap[$g['day_name']] ?? null;
        if ($dayIndex !== null) {
            $networkData[$g['network']][$dayIndex] = $g['total'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">
    <title>WakalaSmart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 2;
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            background: #301a1a;
        }
        header {
            background: #d50000;
            color: white;
            padding: 3px;
            text-align: center;
            font-size: 2.6rem;
            font-weight: bold;
        }
        .container {
            display: grid;
            grid-template-columns: 1fr;
            padding: 20px;
        }
        .main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            background: white;
            padding: 2px;
            border-radius: 9px;
            box-shadow: 0 4px 10px rgba(16, 117, 116, 0.29);
        }
        .card h3 {
            margin-top: 0;
            color: #d50000;
        }
        .balance-value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .form-card {
            background:lightblue;
            width: 360px;
            margin: 30px auto;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .form-card input,
        .form-card select,
        .form-card textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }
        .form-card button {
            background: #d50000;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            margin-top: 10px;
        }
        .menu-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            font-size: 28px;
            color: white;
            cursor: pointer;
            z-index: 1001;
        }
        .side-menu {
            position: fixed;
            top: 0;
            left: -260px;
            width: 200px;
            height: 75%;
            background: #928b8b;
            padding-top: 70px;
            transition: 0.3s;
            z-index: 1000;
            overflow-y: auto;
        }
        .side-menu.active {
            left: 0;
        }
        .side-menu a {
            display: block;
            color: white;
            text-decoration:none;
            padding: 10px 25px;
            border-bottom: 1px solid #787878;
            font-size: 15px;
        }
        .side-menu a:hover {
            background: #9ca1a0;
        }
    </style>
</head>
<body>

    <!-- MENU BUTTON -->
    <div class="menu-btn"onclick="toggleMenu()">☰</div>

    <!-- SIDEBAR -->
    <div class="side-menu"id="sideMenu">
        <a href="#" onclick="toggleForm('mtajiForm')">Ongeza Mtaji</a>
        <a href="#" onclick="toggleForm('distributionForm')">Sambaza Float</a>
        <a href="#" onclick="toggleForm('withdrawForm')">Kutoa</a>
        <a href="#" onclick="toggleForm('depositForm')">Kutuma</a>
        <a href="#" onclick="toggleForm('convertLipaCash')">LIPA kuwa Cash</a>
        <a href="#" onclick="toggleForm('lipaForm')">LIPA kwa Simu</a>
        <a href="#" onclick="toggleForm('floatTransferForm')">Float kwa Float</a>
        <a href="#" onclick="toggleBankMenu()">Huduma za Benki</a>

        <div id="bankSidebarMenu"style="display:none; background:#7f7f7f;">
            <?php foreach ($banks as $bank): ?>
                <a href="#"
                    onclick="toggleForm('<?= strtolower($bank) ?>BankForm')">
                    <?= $bank ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- HEADER -->
    <header style="position:relative;">
        <img src=""
            class="logo">WSMS

        <!-- TOP RIGHT -->
        <div style="position:absolute;
                    top:20px;
                    right:20px;
                    display:flex;
                    align-items:center;
                    gap:15px;">
            <span style="font-size:14px;color:white;">
              <?= date('D, d M Y'); ?>
            </span>
            <button onclick="openNewDayBox()"
                style="background:white;
                       color:#d50000;
                       padding:6px 14px;
                       border:none;
                       border-radius:6px;
                       font-size:13px;
                       font-weight:bold;
                       cursor:pointer;">New
            </button>
        </div>
    </header>
    <!-- DASHBOARD -->
    <div class="container"
        id="dashboardSection">
        <div class="main">

            <!-- CASH -->
            <div class="card">
                <h3>Cash on Hand</h3>
                <div class="balance-value">
                    Tsh <?= number_format($balances['CASH']['Cash_on_hand'] ?? 0, 2) ?>
                </div>
            </div>

            <!-- NETWORK CARDS -->
            <?php foreach (['Till', 'Lipa'] as $type): ?>
                <?php foreach ($networks as $network): ?>
                    <div class="card">
                        <h3><?= $network ?> - <?= $type ?></h3>
                        <div class="balance-value">
                            Tsh <?= number_format($balances[$network][$type] ?? 0, 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>

            <!-- MTAJI -->
            <div class="card">
                <h3>J/Kuu</h3>
                <div class="balance-value">
                    Tsh <?= number_format($Mtaji, 2) ?>
                </div>
            </div>

            <!-- PROFIT -->
            <div class="card">
                <h3>Faida Leo (LIPA)</h3>
                <div class="balance-value">
                    Tsh <?= number_format($profit, 2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- WITHDRAW -->
    <div class="form-card"id="withdrawForm"style="display:none;">
        <h3>Mteja Anatoa Pesa</h3>
        <form action="process_transaction.php"method="post">
            <input type="hidden" name="type" value="withdrawal">
            <select name="network" required>
                <option value="">Chagua Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="amount" placeholder="Kiasi" required>
            <button type="submit">Thibitisha</button>
            <button type="button"onclick="showDashboard()">Back</button>
        </form>
    </div>

    <!-- DEPOSIT -->
    <div class="form-card"id="depositForm"style="display:none;">
        <h3>Mteja Anaweka Pesa</h3>
        <form action="process_transaction.php"method="post">
            <input type="hidden"name="type"value="deposit">
            <select name="network" required>
                <option value="">Chagua Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number"name="amount"placeholder="Weka Kiasi(Tsh.)"required>
            <button type="submit">Thibitisha</button>
            <button type="button"onclick="showDashboard()">Back</button>
        </form>
    </div>

    <!-- LIPA -->
    <div class="form-card"id="lipaForm"style="display:none;">
        <h3>LIPA Kwa Simu</h3>
        <form action="process_transaction.php"method="post">
            <input type="hidden"name="type"value="lipa_cashout">
            <select name="network" required>
                <option value="">Chagua Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number"name="amount"placeholder="Pesa aliyopewa Mteja(Tsh.)"required>
            <input type="number"name="actual_lipa_amount" placeholder="Pesa iliyolipa Mteja(Tsh.)"required>
            <button type="submit">Thibitisha</button>
            <button type="button"onclick="showDashboard()">Back</button>
        </form>
    </div>

    <!-- LIPA TO CASH -->
    <div class="form-card"id="convertLipaCash"style="display:none;">
        <h3>LIPA kuwa Cash</h3>
        <form action="process_transaction.php"method="post">
            <input type="hidden"name="type"value="convert_lipa_to_cash">
            <select name="network" required>
                <option value="">Chagua Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number"name="amount"placeholder="Weka Kiasi(Tsh.)"required>
            <button type="submit">Badilisha</button>
            <button type="button"onclick="showDashboard()">Back </button>
        </form>
    </div>

    <!-- MTAJI -->
    <div class="form-card"id="mtajiForm"style="display:none;">
        <h3>Ongeza Mtaji</h3>
        <form action="process_mtaji.php"method="post">
            <input type="number"name="amount"placeholder="Weka Kiasi(Tsh.)"required>
            <button type="submit">Ongeza</button>
            <button type="button"onclick="showDashboard()">Back</button>
        </form>
    </div>

    <!-- DISTRIBUTION -->
    <div class="form-card"id="distributionForm"style="display:none;">
        <h3>Sambaza Float</h3>
        <form action="distribute_float.php"method="post">
            <select name="network" required>
                <option value="">Chagua Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <select name="account_type" required>
                <option value="Till">Till</option>
            </select>
            <input type="number"name="amount"placeholder="Weka Kiasi(Tsh.)"required>
            <button type="submit">Sambaza</button>
            <button type="button"onclick="showDashboard()">Back</button>
        </form>
    </div>

    <!-- FLOAT KWA FLOAT -->
    <div class="form-card"id="floatTransferForm"style="display:none;">
        <h3>Float kwa Float</h3>
        <form action="process_transaction.php"method="post">
            <input type="hidden"name="type"value="float_transfer">
            <select name="from_network" required>
                <option value="">Kutoka Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <select name="to_network" required>
                <option value="">Kwenda Mtandao</option>
                <?php foreach ($networks as $network): ?>
                    <option><?= $network ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number"name="amount"placeholder="Weka Kiasi(Tsh.)"required>
            <button type="submit"> Hamisha Float</button>
            <button type="button"onclick="showDashboard()">Back</button>
        </form>
    </div>

    <!-- BANK FORMS -->
    <?php foreach ($banks as $bank): ?>
        <div class="form-card"id="<?= strtolower($bank) ?>BankForm"style="display:none;">
            <h3><?= $bank ?> SERVICES</h3>
            <form action="process_bank.php"method="post">
                <input type="hidden"name="bank"value="<?= $bank ?>">
                <label>Choose Service</label>
                <select name="service" required>
                    <option value="">Select Service</option>
                    <option value="Deposit">Deposit</option>
                    <option value="Withdraw">Withdraw</option>
                </select>
                <label>Amount</label>
                <input type="number"name="amount"placeholder="Weka Kiasi(Tsh.)">
                <button type="submit">Continue</button>
                <button type="button"
                    onclick="showDashboard()">Back</button>
            </form>
        </div>
    <?php endforeach; ?>
    <script>
        /* =========================
            SIDEBAR
        ========================= */
        function toggleMenu() {document.getElementById("sideMenu").classList.toggle("active");}

        /* =========================
           BANK MENU
        ========================= */
        function toggleBankMenu() {

            const menu =
                document.getElementById(
                    "bankSidebarMenu"
                );

            if (menu.style.display === "block") {

                menu.style.display = "none";

            } else {

                menu.style.display = "block";
            }
        }

        /* =========================
           FORMS
        ========================= */
        const forms = [
            'withdrawForm',
            'depositForm',
            'lipaForm',
            'convertCashForm',
            'convertLipaCash',
            'mtajiForm',
            'distributionForm',
            'floatTransferForm',
            'nmbBankForm',
            'crdbBankForm',
            'nbcBankForm',];

        /* =========================
           SHOW FORM
        ========================= */
        function toggleForm(id) {

            document.getElementById(
                "dashboardSection"
            ).style.display = "none";

            forms.forEach(function(formId) {

                const element =
                    document.getElementById(formId);

                if (element) {

                    element.style.display = "none";
                }
            });

            const selected =
                document.getElementById(id);

            if (selected) {

                selected.style.display = "block";
            }

            document
                .getElementById("sideMenu")
                .classList
                .remove("active");
        }

        /* =========================
           SHOW DASHBOARD
        ========================= */
        function showDashboard() {

            document.getElementById(
                "dashboardSection"
            ).style.display = "grid";

            forms.forEach(function(formId) {
                const element =document.getElementById(formId);
                if (element) {element.style.display = "none";}
            });
        }
        /* =========================
           NEW DAY POPUP
        ========================= */

        function openNewDayBox() {

            document.getElementById(
                "newDayPopup"
            ).style.display = "block";
        }

        function closeNewDayBox() {

            document.getElementById(
                "newDayPopup"
            ).style.display = "none";
        }

        function startNewDay() {

            localStorage.removeItem("activeForms");

            document.querySelectorAll('.form-card')
                .forEach(function(form) {

                    form.style.display = "none";
                });

            document.getElementById(
                "dashboardSection"
            ).style.display = "grid";

            closeNewDayBox();

            alert(
                "New day started successfully."
            );
            location.reload();
        }
        function startNewDay() {

            if (confirm("Start a completely new day?")) {

                window.location.href = 'start_new_day.php';
            }
        }
        /* =========================
           BAR GRAPH
        ========================= */
        const ctx = document.getElementById('transactionChart');
        new Chart(ctx, {
            type: 'graph',
            data: {
                labels: <?= json_encode($days) ?>,
                datasets: [
                    {
                        label: 'Airtel',
                        data: <?= json_encode($networkData['Airtel']) ?>,
                        backgroundColor: '#e5383549'
                    },
                    {
                        label: 'Yas',
                        data: <?= json_encode($networkData['Yas']) ?>,
                        backgroundColor: '#fdfd0f'
                    },
                    {
                        label: 'Vodacom',
                        data: <?= json_encode($networkData['Vodacom']) ?>,
                        backgroundColor: '#f40e0e'
                    },
                    {
                        label: 'Halotel',
                        data: <?= json_encode($networkData['Halotel']) ?>,
                        backgroundColor: '#fb8c00'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {  legend: {position: 'bottom'}},
                scales: { y: { beginAtZero: true}}
            }
        });
    </script>
    <!-- NEW DAY POPUP -->
    <div id="newDayPopup"
        style= "display:none;
                position:fixed;
                top:0;
                left:0;
                width:100%;
                height:100%;
                background:rgba(0,0,0,0.5);
                z-index:3000;">
    <div 
        style="background:white;
                width:320px;
                padding:25px;
                border-radius:10px;
                position:absolute;
                top:50%;
                left:50%;
                transform:translate(-50%,-50%);
                text-align:center;">
            <h3 style="color:#d50000;">New</h3>
            <p>Are you sure you want to start a new dashboard session???????</p>
            <div style="margin-top:20px;
            display:flex;
            justify-content:center;
            gap:15px;">

        <button onclick="startNewDay()"
        style="background:green;
               color:white;
               border:none;
                padding:10px 20px;
                border-radius:5px;
                 cursor:pointer;">yes
        </button>
                <button onclick="closeNewDayBox()"
                    style="background:red;
                    color:white;
                    border:none;
                    padding:10px 20px;
                    border-radius:5px;
                    cursor:pointer;">no
                </button>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>