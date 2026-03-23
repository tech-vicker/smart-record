<?php
session_start();
require_once 'db.php';

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($db) {
    if (!isset($_SESSION['user_id'])) return ['name' => 'User', 'farm_name' => 'Farm', 'email' => ''];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    return $user ?: ['name' => 'User', 'farm_name' => 'Farm', 'email' => ''];
}

function getUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getCurrentFarm($db) {
    if (!isset($_SESSION['user_id'])) return null;
    
    // Check if a farm is selected in session
    if (isset($_SESSION['farm_id'])) {
        $stmt = $db->prepare("SELECT * FROM farms WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $_SESSION['farm_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $farm = $result->fetchArray(SQLITE3_ASSOC);
        if ($farm) return $farm;
    }
    
    // Get first farm for user
    $stmt = $db->prepare("SELECT * FROM farms WHERE user_id = :user_id LIMIT 1");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    $farm = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($farm) {
        $_SESSION['farm_id'] = $farm['id'];
        return $farm;
    }
    
    return null;
}

function getFarms($db) {
    if (!isset($_SESSION['user_id'])) return [];
    
    $stmt = $db->prepare("SELECT * FROM farms WHERE user_id = :user_id ORDER BY name");
    $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $farms = [];
    while ($farm = $result->fetchArray(SQLITE3_ASSOC)) {
        $farms[] = $farm;
    }
    
    return $farms;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFarm - Farm Record Keeping</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if (isLoggedIn()): 
        $user = getCurrentUser($db);
        $current_farm = getCurrentFarm($db);
        $farms = getFarms($db);
    ?>
    <nav class="navbar">
        <div class="nav-brand">
            <span>🌾 SmartFarm</span>
            <?php if ($current_farm): ?>
                <small><?php echo htmlspecialchars($current_farm['name']); ?></small>
            <?php endif; ?>
        </div>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <?php if (count($farms) > 1): ?>
            <select class="farm-selector" onchange="switchFarm(this.value)">
                <?php foreach ($farms as $farm): ?>
                    <option value="<?php echo $farm['id']; ?>" <?php echo ($current_farm && $current_farm['id'] == $farm['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($farm['name']); ?>
                    </option>
                <?php endforeach; ?>
                <option value="manage">+ Manage Farms</option>
            </select>
            <?php endif; ?>
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">
                <span id="theme-icon">🌙</span>
            </button>
            <ul class="nav-menu">
                <li><a href="home.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <li><a href="livestock.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'livestock.php' ? 'active' : ''; ?>">Livestock</a></li>
                <li><a href="crops.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'crops.php' ? 'active' : ''; ?>">Crops</a></li>
                <li><a href="tasks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>">Tasks</a></li>
                <li><a href="calendar.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'active' : ''; ?>">📅 Calendar</a></li>
                <li><a href="finances.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'finances.php' ? 'active' : ''; ?>">Finances</a></li>
                <li><a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">Analytics</a></li>
                <li><a href="farms.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'farms.php' ? 'active' : ''; ?>">🏡 Farms</a></li>
                <li><a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">Profile</a></li>
                <li><a href="logout.php" class="logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    <main class="main-content">
    <?php endif; ?>
