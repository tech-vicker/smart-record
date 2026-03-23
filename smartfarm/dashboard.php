<?php
require_once 'includes/header.php';
requireAuth();

$user = getCurrentUser($db);
$user_id = getUserId();
$current_farm = getCurrentFarm($db);
$farm_id = $current_farm ? $current_farm['id'] : 0;

// Get statistics
$stmt = $db->prepare("SELECT SUM(count) FROM livestock WHERE user_id = :user_id AND farm_id = :farm_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$livestock_count = $stmt->execute()->fetchArray()[0] ?? 0;

$stmt = $db->prepare("SELECT COUNT(*) FROM crops WHERE user_id = :user_id AND farm_id = :farm_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$crops_count = $stmt->execute()->fetchArray()[0] ?? 0;

$stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND farm_id = :farm_id AND completed = 0");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$pending_tasks = $stmt->execute()->fetchArray()[0] ?? 0;

// Get total income and expenses
$stmt = $db->prepare("SELECT SUM(amount) FROM finances WHERE user_id = :user_id AND farm_id = :farm_id AND type = 'income'");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$income = $stmt->execute()->fetchArray()[0] ?? 0;

$stmt = $db->prepare("SELECT SUM(amount) FROM finances WHERE user_id = :user_id AND farm_id = :farm_id AND type = 'expense'");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$expenses = $stmt->execute()->fetchArray()[0] ?? 0;
$balance = $income - $expenses;

// Get recent activities
$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = :user_id AND farm_id = :farm_id AND completed = 0 ORDER BY due_date ASC LIMIT 5");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$recent_tasks = $stmt->execute();
?>

<div class="dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>!</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🐄</div>
            <div class="stat-info">
                <h3><?php echo $livestock_count; ?></h3>
                <p>Livestock</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🌱</div>
            <div class="stat-info">
                <h3><?php echo $crops_count; ?></h3>
                <p>Crops</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✓</div>
            <div class="stat-info">
                <h3><?php echo $pending_tasks; ?></h3>
                <p>Pending Tasks</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
                <h3>$<?php echo number_format($balance, 2); ?></h3>
                <p>Balance</p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-sections">
        <div class="section">
            <h2>📋 Pending Tasks</h2>
            <?php if ($recent_tasks->numColumns() && $recent_tasks->fetchArray(SQLITE3_ASSOC)): 
                $recent_tasks->reset();
                while ($task = $recent_tasks->fetchArray(SQLITE3_ASSOC)): ?>
                <div class="task-item">
                    <div class="task-info">
                        <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                        <span class="priority priority-<?php echo $task['priority']; ?>"><?php echo $task['priority']; ?></span>
                    </div>
                    <small>Due: <?php echo $task['due_date']; ?></small>
                </div>
            <?php endwhile; else: ?>
                <p>No pending tasks. <a href="tasks.php">Add a task</a></p>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>💵 Financial Summary</h2>
            <div class="finance-summary">
                <div class="finance-item income">
                    <span>Income</span>
                    <strong>$<?php echo number_format($income, 2); ?></strong>
                </div>
                <div class="finance-item expense">
                    <span>Expenses</span>
                    <strong>$<?php echo number_format($expenses, 2); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

</main>
<script src="js/app.js"></script>
</body>
</html>
