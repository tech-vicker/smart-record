<?php
require_once 'includes/header.php';
requireAuth();

$user_id = getUserId();
$current_farm = getCurrentFarm($db);
$farm_id = $current_farm ? $current_farm['id'] : 0;

// Get statistics
$stmt = $db->prepare("SELECT type, SUM(count) as total FROM livestock WHERE user_id = :user_id AND farm_id = :farm_id GROUP BY type");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$livestock_by_type = $stmt->execute();
$stmt = $db->prepare("SELECT status, COUNT(*) as total FROM crops WHERE user_id = :user_id AND farm_id = :farm_id GROUP BY status");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$crops_by_status = $stmt->execute();
$stmt = $db->prepare("SELECT strftime('%Y-%m', date) as month, type, SUM(amount) as total FROM finances WHERE user_id = :user_id AND farm_id = :farm_id GROUP BY month, type ORDER BY month DESC LIMIT 12");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$monthly_finances = $stmt->execute();
$stmt = $db->prepare("SELECT priority, COUNT(*) as total FROM tasks WHERE user_id = :user_id AND farm_id = :farm_id AND completed = 0 GROUP BY priority");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$tasks_by_priority = $stmt->execute();

$stmt = $db->prepare("SELECT SUM(count) FROM livestock WHERE user_id = :user_id AND farm_id = :farm_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$total_livestock = $stmt->execute()->fetchArray()[0] ?? 0;
$stmt = $db->prepare("SELECT COUNT(*) FROM crops WHERE user_id = :user_id AND farm_id = :farm_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$total_crops = $stmt->execute()->fetchArray()[0] ?? 0;
$stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND farm_id = :farm_id");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$total_tasks = $stmt->execute()->fetchArray()[0] ?? 0;
$stmt = $db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND farm_id = :farm_id AND completed = 1");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$completed_tasks = $stmt->execute()->fetchArray()[0] ?? 0;
$task_completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;

// Monthly income/expense data for chart
$months = [];
$income_data = [];
$expense_data = [];
$stmt = $db->prepare("SELECT strftime('%Y-%m', date) as month, type, SUM(amount) as total FROM finances WHERE user_id = :user_id AND farm_id = :farm_id GROUP BY month, type ORDER BY month ASC LIMIT 6");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
$temp_finances = $stmt->execute();
while ($row = $temp_finances->fetchArray(SQLITE3_ASSOC)) {
    if (!in_array($row['month'], $months)) {
        $months[] = $row['month'];
        $income_data[$row['month']] = 0;
        $expense_data[$row['month']] = 0;
    }
    if ($row['type'] == 'income') {
        $income_data[$row['month']] = $row['total'];
    } else {
        $expense_data[$row['month']] = $row['total'];
    }
}
?>

<div class="page-header">
    <h1>📊 Analytics Dashboard</h1>
</div>

<div class="analytics-grid">
    <!-- Summary Cards -->
    <div class="analytics-card">
        <h3>Farm Overview</h3>
        <div class="stat-grid">
            <div class="mini-stat">
                <span class="mini-number"><?php echo $total_livestock; ?></span>
                <span class="mini-label">Animals</span>
            </div>
            <div class="mini-stat">
                <span class="mini-number"><?php echo $total_crops; ?></span>
                <span class="mini-label">Crops</span>
            </div>
            <div class="mini-stat">
                <span class="mini-number"><?php echo $task_completion_rate; ?>%</span>
                <span class="mini-label">Task Completion</span>
            </div>
        </div>
    </div>

    <!-- Livestock Distribution -->
    <div class="analytics-card">
        <h3>Livestock by Type</h3>
        <div class="chart-list">
            <?php while ($row = $livestock_by_type->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="chart-item">
                <span class="chart-label"><?php echo ucfirst($row['type']); ?></span>
                <div class="bar-container">
                    <div class="bar" style="width: <?php echo $total_livestock > 0 ? ($row['total'] / $total_livestock * 100) : 0; ?>%"></div>
                </div>
                <span class="chart-value"><?php echo $row['total']; ?></span>
            </div>
            <?php endwhile; ?>
            <?php if ($total_livestock == 0): ?>
                <p class="empty-state">No livestock records yet</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Crop Status -->
    <div class="analytics-card">
        <h3>Crop Status</h3>
        <div class="status-grid">
            <?php 
            $status_colors = ['planted' => '#3498db', 'growing' => '#2ecc71', 'harvested' => '#f39c12', 'fallow' => '#95a5a6'];
            while ($row = $crops_by_status->fetchArray(SQLITE3_ASSOC)): 
            ?>
            <div class="status-item">
                <div class="status-color" style="background: <?php echo $status_colors[$row['status']] ?? '#ccc'; ?>"></div>
                <span class="status-name"><?php echo ucfirst($row['status']); ?></span>
                <span class="status-count"><?php echo $row['total']; ?></span>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Task Priority -->
    <div class="analytics-card">
        <h3>Pending Tasks by Priority</h3>
        <div class="priority-list">
            <?php while ($row = $tasks_by_priority->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="priority-item">
                <span class="priority-badge priority-<?php echo $row['priority']; ?>"><?php echo ucfirst($row['priority']); ?></span>
                <span class="priority-count"><?php echo $row['total']; ?> tasks</span>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Monthly Finances -->
    <div class="analytics-card wide">
        <h3>Monthly Finances</h3>
        <div class="finance-chart">
            <?php foreach ($months as $month): 
                $inc = $income_data[$month] ?? 0;
                $exp = $expense_data[$month] ?? 0;
                $max = max($inc, $exp, 1);
            ?>
            <div class="finance-bar-group">
                <div class="finance-bars">
                    <div class="finance-bar income-bar" style="height: <?php echo ($inc / max(array_merge($income_data, $expense_data) ?: [1]) * 100); ?>%"></div>
                    <div class="finance-bar expense-bar" style="height: <?php echo ($exp / max(array_merge($income_data, $expense_data) ?: [1]) * 100); ?>%"></div>
                </div>
                <span class="finance-month"><?php echo date('M', strtotime($month)); ?></span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($months)): ?>
                <p class="empty-state">No financial records yet</p>
            <?php endif; ?>
        </div>
        <div class="finance-legend">
            <span class="legend-item"><span class="legend-color income"></span> Income</span>
            <span class="legend-item"><span class="legend-color expense"></span> Expenses</span>
        </div>
    </div>
</div>

</main>
<script src="js/app.js"></script>
</body>
</html>
