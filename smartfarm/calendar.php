<?php
require_once 'includes/header.php';
requireAuth();

$user_id = getUserId();
$message = '';

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Get tasks for the current month
$stmt = $db->prepare("SELECT * FROM tasks WHERE user_id = :user_id AND strftime('%Y-%m', due_date) = :year_month ORDER BY due_date");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$stmt->bindValue(':year_month', sprintf('%04d-%02d', $year, $month), SQLITE3_TEXT);
$tasks = $stmt->execute();

// Organize tasks by date
$tasks_by_date = [];
while ($task = $tasks->fetchArray(SQLITE3_ASSOC)) {
    $day = date('j', strtotime($task['due_date']));
    $tasks_by_date[$day][] = $task;
}

// Calendar navigation
$prev_month = $month - 1;
$next_month = $month + 1;
$prev_year = $year;
$next_year = $year;

if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$first_day_of_month = date('N', mktime(0, 0, 0, $month, 1, $year));
$month_name = date('F', mktime(0, 0, 0, $month, 1, $year));
?>

<div class="page-header">
    <h1>📅 Task Calendar</h1>
    <div class="header-actions">
        <a href="tasks.php" class="btn btn-secondary">← Back to Tasks</a>
        <button class="btn btn-primary" onclick="document.getElementById('addTaskForm').style.display='block'">+ Add Task</button>
    </div>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<!-- Calendar Navigation -->
<div class="calendar-nav">
    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-secondary">← Previous</a>
    <h2><?php echo $month_name . ' ' . $year; ?></h2>
    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-secondary">Next →</a>
</div>

<!-- Calendar Grid -->
<div class="calendar-container">
    <div class="calendar-grid">
        <!-- Day headers -->
        <div class="calendar-day-header">Mon</div>
        <div class="calendar-day-header">Tue</div>
        <div class="calendar-day-header">Wed</div>
        <div class="calendar-day-header">Thu</div>
        <div class="calendar-day-header">Fri</div>
        <div class="calendar-day-header">Sat</div>
        <div class="calendar-day-header">Sun</div>
        
        <!-- Empty cells before first day -->
        <?php for ($i = 1; $i < $first_day_of_month; $i++): ?>
            <div class="calendar-day empty"></div>
        <?php endfor; ?>
        
        <!-- Calendar days -->
        <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
            <div class="calendar-day <?php echo isset($tasks_by_date[$day]) ? 'has-tasks' : ''; ?>">
                <div class="calendar-day-number"><?php echo $day; ?></div>
                <?php if (isset($tasks_by_date[$day])): ?>
                    <div class="calendar-tasks">
                        <?php foreach ($tasks_by_date[$day] as $task): ?>
                            <div class="calendar-task priority-<?php echo $task['priority']; ?> <?php echo $task['completed'] ? 'completed' : ''; ?>">
                                <span class="task-title"><?php echo htmlspecialchars($task['title']); ?></span>
                                <span class="task-category"><?php echo htmlspecialchars($task['category']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Task Details Modal -->
<div id="taskDetailsModal" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('taskDetailsModal').style.display='none'">&times;</span>
        <h2>Task Details</h2>
        <div id="taskDetailsContent"></div>
    </div>
</div>

<!-- Add Task Form (reuse from tasks.php) -->
<div id="addTaskForm" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addTaskForm').style.display='none'">&times;</span>
        <h2>Add Task</h2>
        <form method="POST" action="tasks.php">
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

</main>
<style>
.calendar-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    background: var(--card-bg);
    padding: 1rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.calendar-nav h2 {
    color: var(--primary);
    margin: 0;
}

.calendar-container {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: var(--border);
}

.calendar-day-header {
    background: var(--primary);
    color: white;
    padding: 1rem;
    text-align: center;
    font-weight: bold;
    font-size: 0.9rem;
}

.calendar-day {
    background: var(--card-bg);
    min-height: 100px;
    padding: 0.5rem;
    position: relative;
}

.calendar-day.empty {
    background: var(--bg);
}

.calendar-day.has-tasks {
    background: var(--bg);
}

.calendar-day-number {
    font-weight: bold;
    color: var(--text);
    margin-bottom: 0.5rem;
}

.calendar-tasks {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.calendar-task {
    background: var(--card-bg);
    padding: 0.25rem;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid;
}

.calendar-task.priority-high {
    border-left-color: var(--danger);
}

.calendar-task.priority-medium {
    border-left-color: var(--warning);
}

.calendar-task.priority-low {
    border-left-color: var(--success);
}

.calendar-task.completed {
    opacity: 0.6;
}

.calendar-task:hover {
    transform: scale(1.02);
    box-shadow: var(--shadow);
}

.task-title {
    display: block;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-category {
    display: block;
    font-size: 0.7rem;
    color: var(--text-light);
}

@media (max-width: 768px) {
    .calendar-grid {
        grid-template-columns: repeat(7, 1fr);
        gap: 0;
    }
    
    .calendar-day {
        min-height: 80px;
        padding: 0.25rem;
    }
    
    .calendar-task {
        font-size: 0.65rem;
    }
}
</style>
<script src="js/app.js"></script>
</body>
</html>
