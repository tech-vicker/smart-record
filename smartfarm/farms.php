<?php
require_once 'includes/header.php';
requireAuth();

$user_id = getUserId();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_farm'])) {
        $name = $_POST['name'];
        $location = $_POST['location'];
        $size_acres = $_POST['size_acres'];
        $description = $_POST['description'];
        
        $stmt = $db->prepare("INSERT INTO farms (user_id, name, location, size_acres, description) VALUES (:user_id, :name, :location, :size_acres, :description)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':location', $location, SQLITE3_TEXT);
        $stmt->bindValue(':size_acres', $size_acres, SQLITE3_FLOAT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            $message = 'Farm added successfully';
        } else {
            $error = 'Failed to add farm';
        }
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM farms WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $message = 'Farm deleted successfully';
            // Clear session farm_id if it was the deleted farm
            if (isset($_SESSION['farm_id']) && $_SESSION['farm_id'] == $id) {
                unset($_SESSION['farm_id']);
            }
        } else {
            $error = 'Failed to delete farm';
        }
    }
}

// Get farms
$stmt = $db->prepare("SELECT * FROM farms WHERE user_id = :user_id ORDER BY name");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$farms = $stmt->execute();
?>

<div class="page-header">
    <h1>🏡 Farm Management</h1>
    <button class="btn btn-primary" onclick="document.getElementById('addForm').style.display='block'">+ Add Farm</button>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Add Form -->
<div id="addForm" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addForm').style.display='none'">&times;</span>
        <h2>Add New Farm</h2>
        <form method="POST">
            <div class="form-group">
                <label>Farm Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="City, State/Country">
            </div>
            <div class="form-group">
                <label>Size (Acres)</label>
                <input type="number" name="size_acres" step="0.1" min="0">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of your farm"></textarea>
            </div>
            <button type="submit" name="add_farm" class="btn btn-primary">Add Farm</button>
        </form>
    </div>
</div>

<!-- Farms List -->
<div class="farms-grid">
    <?php while ($farm = $farms->fetchArray(SQLITE3_ASSOC)): ?>
        <div class="farm-card">
            <div class="farm-header">
                <h3><?php echo htmlspecialchars($farm['name']); ?></h3>
                <span class="farm-badge <?php echo (isset($_SESSION['farm_id']) && $_SESSION['farm_id'] == $farm['id']) ? 'active' : ''; ?>">
                    <?php echo (isset($_SESSION['farm_id']) && $_SESSION['farm_id'] == $farm['id']) ? 'Current' : 'Available'; ?>
                </span>
            </div>
            
            <div class="farm-details">
                <?php if ($farm['location']): ?>
                    <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($farm['location']); ?></p>
                <?php endif; ?>
                
                <?php if ($farm['size_acres']): ?>
                    <p><strong>📏 Size:</strong> <?php echo number_format($farm['size_acres'], 1); ?> acres</p>
                <?php endif; ?>
                
                <?php if ($farm['description']): ?>
                    <p><strong>📝 Description:</strong> <?php echo htmlspecialchars($farm['description']); ?></p>
                <?php endif; ?>
                
                <p><strong>📅 Created:</strong> <?php echo date('M j, Y', strtotime($farm['created_at'])); ?></p>
            </div>
            
            <div class="farm-actions">
                <?php if (!isset($_SESSION['farm_id']) || $_SESSION['farm_id'] != $farm['id']): ?>
                    <button class="btn btn-primary" onclick="switchFarm(<?php echo $farm['id']; ?>)">
                        Switch to This Farm
                    </button>
                <?php endif; ?>
                
                <?php if (count(getFarms($db)) > 1): ?>
                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this farm? All data will be permanently lost.')">
                        <input type="hidden" name="id" value="<?php echo $farm['id']; ?>">
                        <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php if (count(getFarms($db)) === 0): ?>
    <div class="empty-state">
        <h3>No Farms Yet</h3>
        <p>Start by adding your first farm to begin tracking your agricultural activities.</p>
        <button class="btn btn-primary" onclick="document.getElementById('addForm').style.display='block'">
            Add Your First Farm
        </button>
    </div>
<?php endif; ?>

</main>
<style>
.farms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.farm-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.farm-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.farm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.farm-header h3 {
    color: var(--primary);
    margin: 0;
    font-size: 1.3rem;
}

.farm-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

.farm-badge.active {
    background: var(--success);
    color: white;
}

.farm-badge:not(.active) {
    background: var(--secondary);
    color: white;
}

.farm-details p {
    margin-bottom: 0.5rem;
    color: var(--text-light);
}

.farm-details strong {
    color: var(--text);
}

.farm-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--card-bg);
    border-radius: var(--radius);
    margin-top: 2rem;
}

.empty-state h3 {
    color: var(--primary);
    margin-bottom: 1rem;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .farms-grid {
        grid-template-columns: 1fr;
    }
    
    .farm-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .farm-actions {
        flex-direction: column;
    }
}
</style>
<script src="js/app.js"></script>
</body>
</html>
