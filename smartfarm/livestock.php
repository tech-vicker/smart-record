<?php
require_once 'includes/header.php';
requireAuth();

$user_id = getUserId();
$current_farm = getCurrentFarm($db);
$farm_id = $current_farm ? $current_farm['id'] : 0;
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM livestock WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        $message = 'Livestock record deleted successfully';
    } else {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $breed = $_POST['breed'];
        $count = $_POST['count'];
        $health = $_POST['health'];
        $value = $_POST['value'];
        
        $stmt = $db->prepare("INSERT INTO livestock (user_id, farm_id, name, type, breed, count, health, value) VALUES (:user_id, :farm_id, :name, :type, :breed, :count, :health, :value)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':breed', $breed, SQLITE3_TEXT);
        $stmt->bindValue(':count', $count, SQLITE3_INTEGER);
        $stmt->bindValue(':health', $health, SQLITE3_TEXT);
        $stmt->bindValue(':value', $value, SQLITE3_FLOAT);
        $stmt->execute();
        $message = 'Livestock added successfully';
    }
}

// Get livestock records
$stmt = $db->prepare("SELECT * FROM livestock WHERE user_id = :user_id AND farm_id = :farm_id ORDER BY name");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$livestock = $stmt->execute();
?>

<div class="page-header">
    <h1>🐄 Livestock Management</h1>
    <div class="header-actions">
        <button class="btn btn-secondary" onclick="exportLivestock()">📥 Export CSV</button>
        <button class="btn btn-primary" onclick="document.getElementById('addForm').style.display='block'">+ Add Livestock</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Add Form -->
<div id="addForm" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addForm').style.display='none'">&times;</span>
        <h2>Add Livestock</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Name/Tag</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" required>
                        <option value="cattle">Cattle</option>
                        <option value="sheep">Sheep</option>
                        <option value="goat">Goat</option>
                        <option value="pig">Pig</option>
                        <option value="chicken">Chicken</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" name="breed">
                </div>
                <div class="form-group">
                    <label>Count</label>
                    <input type="number" name="count" min="1" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Health Status</label>
                    <select name="health">
                        <option value="healthy">Healthy</option>
                        <option value="sick">Sick</option>
                        <option value="treatment">Under Treatment</option>
                        <option value="quarantine">Quarantine</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estimated Value ($)</label>
                    <input type="number" name="value" step="0.01" min="0">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Livestock</button>
        </form>
    </div>
</div>

<!-- Livestock Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Name/Tag</th>
                <th>Type</th>
                <th>Breed</th>
                <th>Count</th>
                <th>Health</th>
                <th>Value</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($animal = $livestock->fetchArray(SQLITE3_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($animal['name']); ?></td>
                <td><?php echo ucfirst($animal['type']); ?></td>
                <td><?php echo htmlspecialchars($animal['breed']); ?></td>
                <td><?php echo $animal['count']; ?></td>
                <td><span class="health-badge health-<?php echo $animal['health']; ?>"><?php echo ucfirst($animal['health']); ?></span></td>
                <td>$<?php echo number_format($animal['value'], 2); ?></td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                        <input type="hidden" name="id" value="<?php echo $animal['id']; ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</main>
<script>
function exportLivestock() {
    showButtonLoading(event.target);
    
    fetch('export_livestock.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                exportToCSV(data.data, 'livestock_records.csv');
                showNotification('Livestock data exported successfully!', 'success');
            } else {
                showNotification('Export failed: ' + data.error, 'error');
            }
        })
        .catch(error => {
            showNotification('Export failed: ' + error.message, 'error');
        })
        .finally(() => {
            hideButtonLoading(event.target);
        });
}
</script>
<script src="js/app.js"></script>
</body>
</html>
