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
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        $message = 'Task deleted';
    } elseif (isset($_POST['complete'])) {
        $id = $_POST['id'];
        $completed = $_POST['completed'] ? 0 : 1;
        $stmt = $db->prepare("UPDATE tasks SET completed = :completed WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':completed', $completed, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
    } else {
        $title = $_POST['title'];
        $category = $_POST['category'];
        $priority = $_POST['priority'];
        $due_date = $_POST['due_date'];
        
        $stmt = $db->prepare("INSERT INTO tasks (user_id, farm_id, title, category, priority, due_date) VALUES (:user_id, :farm_id, :title, :category, :priority, :due_date)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $stmt->bindValue(':priority', $priority, SQLITE3_TEXT);
        $stmt->bindValue(':due_date', $due_date, SQLITE3_TEXT);
        $stmt->execute();
        $message = 'Task added successfully';
    }
}

$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = :user_id AND farm_id = :farm_id ORDER BY completed ASC, due_date ASC");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$tasks = $stmt->execute();
?>

<div class="page-header">
    <h1>✓ Task Management</h1>
    <button class="btn btn-primary" onclick="document.getElementById('addForm').style.display='block'">+ Add Task</button>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<div id="addForm" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addForm').style.display='none'">&times;</span>
        <h2>Add Task</h2>
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="feeding">Feeding</option>
                        <option value="watering">Watering</option>
                        <option value="milking">Milking</option>
                        <option value="planting">Planting</option>
                        <option value="harvesting">Harvesting</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="veterinary">Veterinary</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Due Date</label>
                <input type="date" name="due_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Task</button>
        </form>
    </div>
</div>

<div class="task-list">
    <?php while ($task = $tasks->fetchArray(SQLITE3_ASSOC)): ?>
    <div class="task-card <?php echo $task['completed'] ? 'completed' : ''; ?>">
        <div class="task-header">
            <form method="POST" style="display:inline">
                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                <input type="hidden" name="completed" value="<?php echo $task['completed']; ?>">
                <button type="submit" name="complete" class="task-checkbox <?php echo $task['completed'] ? 'checked' : ''; ?>">
                    <?php echo $task['completed'] ? '✓' : ''; ?>
                </button>
            </form>
            <div class="task-title-section">
                <span class="task-title <?php echo $task['completed'] ? 'completed' : ''; ?>"><?php echo htmlspecialchars($task['title']); ?></span>
                <span class="task-category"><?php echo ucfirst($task['category']); ?></span>
            </div>
            <span class="priority-badge priority-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
        </div>
        <div class="task-footer">
            <small>Due: <?php echo $task['due_date']; ?></small>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this task?')">
                <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php endwhile; ?>
</div>

</main>
<script src="js/app.js"></script>
</body>
</html>
