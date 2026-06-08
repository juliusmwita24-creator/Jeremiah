<!-- Withdraw Form -->
<div class="form-card" id="withdrawForm" style="display: none;">
    <h3>Muamala: Mteja Anatoa Pesa</h3>
    <form action="process_transaction.php" method="post">
        <input type="hidden" name="type" value="withdrawal">
        <label>Mtandao:</label>
        <select name="network" required>
            <option value="">Chagua Mtandao</option>
            <option value="Airtel">Airtel</option>
            <option value="Yas">Yas</option>
            <option value="Vodacom">Vodacom</option>
            <option value="Halotel">Halotel</option>
        </select>
        <label>Kiasi:</label>
        <input type="number" name="amount" step="0.01" required>
        <label>Maelezo:</label>
        <textarea name="notes"></textarea>
        <button type="submit">Thibitisha</button>
    </form>
</div>

<!-- Deposit Form -->
<div class="form-card" id="depositForm" style="display: none;">
    <h3>Muamala: Mteja Anaweka Pesa</h3>
    <form action="process_transaction.php" method="post">
        <input type="hidden" name="type" value="deposit">
        <label>Mtandao:</label>
        <select name="network" required>
            <option value="">Chagua Mtandao</option>
            <option value="Airtel">Airtel</option>
            <option value="Yas">Yas</option>
            <option value="Vodacom">Vodacom</option>
            <option value="Halotel">Halotel</option>
        </select>
        <label>Kiasi:</label>
        <input type="number" name="amount" step="0.01" required>
        <label>Maelezo:</label>
        <textarea name="notes"></textarea>
        <button type="submit">Thibitisha</button>
    </form>
</div>

<!-- LIPA Cashout -->
<div class="form-card" id="lipaForm" style="display: none;">
    <h3>Muamala: Pokea kwa LIPA</h3>
    <form action="process_transaction.php" method="post">
        <input type="hidden" name="type" value="lipa_cashout">
        <label>Mtandao:</label>
        <select name="network" required>
            <option value="">Chagua Mtandao</option>
            <option value="Airtel">Airtel</option>
            <option value="Yas">Yas</option>
            <option value="Vodacom">Vodacom</option>
            <option value="Halotel">Halotel</option>
        </select>
        <label>Kiasi alicholipwa mteja (Cash):</label>
        <input type="number" name="amount" step="0.01" required>
        <label>Kiasi kilichoingia kwenye LIPA (float):</label>
        <input type="number" name="actual_lipa_amount" step="0.01" required>
        <label>Maelezo:</label>
        <textarea name="notes"></textarea>
        <button type="submit">Thibitisha</button>
    </form>
</div>

<!-- LIPA to TILL Transfer -->
<div class="form-card" id="lipaToTillForm" style="display: none;">
    <h3>Transfer: LIPA kwenda TILL</h3>
    <form action="process_transaction.php" method="post">
        <input type="hidden" name="type" value="transfer_lipa_to_till">
        <label>Mtandao:</label>
        <select name="network" required>
            <option value="">Chagua Mtandao</option>
            <option value="Airtel">Airtel</option>
            <option value="Yas">Tigo<
            /option>
            <option value="Vodacom">Vodacom</option>
            <option value="Halotel">Halotel</option>
        </select>
        <label>Kiasi cha kuhamisha:</label>
        <input type="number" name="amount" step="0.01" required>
        <label>Maelezo:</label>
        <textarea name="notes"></textarea>
        <button type="submit">Hamisha</button>
    </form>
</div>

<!-- Convert Cash to Float -->
<div class="form-card" id="convertCashForm" style="display: none;">
    <h3>Badilisha Cash kuwa Float</h3>
    <form action="process_transaction.php" method="post">
        <input type="hidden" name="type" value="convert_cash_to_float">
        <label>Mtandao:</label>
        <select name="network" required>
            <option value="">Chagua Mtandao</option>
            <option value="Airtel">Airtel</option>
            <option value="Yas">Tigo</option>
            <option value="Vodacom">Vodacom</option>
            <option value="Halotel">Halotel</option>
        </select>
        <label>Kiasi cha kubadilisha:</label>
        <input type="number" name="amount" step="0.01" required>
        <label>Maelezo:</label>
        <textarea name="notes"></textarea>
        <button type="submit">Badilisha</button>
    </form>
</div>
