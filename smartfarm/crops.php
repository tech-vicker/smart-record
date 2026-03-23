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
        $stmt = $db->prepare("DELETE FROM crops WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        $message = 'Crop record deleted successfully';
    } else {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $area = $_POST['area'];
        $planted_date = $_POST['planted_date'];
        $status = $_POST['status'];
        $expected_harvest = $_POST['expected_harvest'];
        
        $stmt = $db->prepare("INSERT INTO crops (user_id, farm_id, name, type, area, planted_date, status, expected_harvest) VALUES (:user_id, :farm_id, :name, :type, :area, :planted_date, :status, :expected_harvest)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':area', $area, SQLITE3_FLOAT);
        $stmt->bindValue(':planted_date', $planted_date, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':expected_harvest', $expected_harvest, SQLITE3_TEXT);
        $stmt->execute();
        $message = 'Crop added successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM crops WHERE user_id = :user_id AND farm_id = :farm_id ORDER BY planted_date DESC");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$crops = $stmt->execute();
?>

<div class="page-header">
    <h1>🌱 Crop Management</h1>
    <button class="btn btn-primary" onclick="document.getElementById('addForm').style.display='block'">+ Add Crop</button>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<div id="addForm" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addForm').style.display='none'">&times;</span>
        <h2>Add Crop</h2>
        <form method="POST">
            <div class="form-group">
                <label>Crop Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Area (acres)</label>
                    <input type="number" name="area" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        <option value="planted">Planted</option>
                        <option value="growing">Growing</option>
                        <option value="harvested">Harvested</option>
                        <option value="fallow">Fallow</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Planted Date</label>
                    <input type="date" name="planted_date" required>
                </div>
                <div class="form-group">
                    <label>Expected Harvest</label>
                    <input type="date" name="expected_harvest">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Crop</button>
        </form>
    </div>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Crop</th>
                <th>Area (acres)</th>
                <th>Planted Date</th>
                <th>Status</th>
                <th>Expected Harvest</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($crop = $crops->fetchArray(SQLITE3_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($crop['name']); ?></td>
                <td><?php echo $crop['area']; ?></td>
                <td><?php echo $crop['planted_date']; ?></td>
                <td><span class="status-badge status-<?php echo $crop['status']; ?>"><?php echo ucfirst($crop['status']); ?></span></td>
                <td><?php echo $crop['expected_harvest']; ?></td>
                <td>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this crop?')">
                        <input type="hidden" name="id" value="<?php echo $crop['id']; ?>">
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
