<?php
require_once 'includes/header.php';
requireAuth();

$user_id = getUserId();
$current_farm = getCurrentFarm($db);
$farm_id = $current_farm ? $current_farm['id'] : 0;
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM finances WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->execute();
        $message = 'Record deleted';
    } else {
        $type = $_POST['type'];
        $category = $_POST['category'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $payment_method = $_POST['payment_method'];
        
        $stmt = $db->prepare("INSERT INTO finances (user_id, farm_id, type, category, amount, date, description, payment_method) VALUES (:user_id, :farm_id, :type, :category, :amount, :date, :description, :payment_method)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $stmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':payment_method', $payment_method, SQLITE3_TEXT);
        $stmt->execute();
        $message = 'Record added successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM finances WHERE user_id = :user_id AND farm_id = :farm_id ORDER BY date DESC");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$finances = $stmt->execute();
$stmt = $db->prepare("SELECT SUM(amount) FROM finances WHERE user_id = :user_id AND farm_id = :farm_id AND type = 'income'");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$income = $stmt->execute()->fetchArray()[0] ?? 0;
$stmt = $db->prepare("SELECT SUM(amount) FROM finances WHERE user_id = :user_id AND farm_id = :farm_id AND type = 'expense'");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$expenses = $stmt->execute()->fetchArray()[0] ?? 0;
?>

<div class="page-header">
    <h1>💵 Financial Records</h1>
    <button class="btn btn-primary" onclick="document.getElementById('addForm').style.display='block'">+ Add Transaction</button>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<div class="finance-summary-header">
    <div class="summary-box income-box">
        <span>Total Income</span>
        <strong>$<?php echo number_format($income, 2); ?></strong>
    </div>
    <div class="summary-box expense-box">
        <span>Total Expenses</span>
        <strong>$<?php echo number_format($expenses, 2); ?></strong>
    </div>
    <div class="summary-box balance-box">
        <span>Balance</span>
        <strong>$<?php echo number_format($income - $expenses, 2); ?></strong>
    </div>
</div>

<div id="addForm" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addForm').style.display='none'">&times;</span>
        <h2>Add Transaction</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="sales">Sales</option>
                        <option value="livestock">Livestock</option>
                        <option value="crops">Crops</option>
                        <option value="feed">Feed</option>
                        <option value="equipment">Equipment</option>
                        <option value="labor">Labor</option>
                        <option value="veterinary">Veterinary</option>
                        <option value="seeds">Seeds</option>
                        <option value="fertilizer">Fertilizer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Amount ($)</label>
                    <input type="number" name="amount" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="mobile">Mobile Money</option>
                        <option value="credit">Credit</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Transaction</button>
        </form>
    </div>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $finances->fetchArray(SQLITE3_ASSOC)): ?>
            <tr>
                <td><?php echo $item['date']; ?></td>
                <td><span class="type-badge type-<?php echo $item['type']; ?>"><?php echo ucfirst($item['type']); ?></span></td>
                <td><?php echo ucfirst($item['category']); ?></td>
                <td><?php echo htmlspecialchars($item['description']); ?></td>
                <td class="<?php echo $item['type']; ?>">$<?php echo number_format($item['amount'], 2); ?></td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</main>
<script src="js/app.js"></script>
</body>
</html>
