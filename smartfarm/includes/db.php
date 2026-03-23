<?php
$db = new SQLite3(__DIR__ . '/../db/database.sqlite');

// Enable performance optimizations
$db->exec('PRAGMA journal_mode = WAL');
$db->exec('PRAGMA synchronous = NORMAL');
$db->exec('PRAGMA cache_size = 10000');
$db->exec('PRAGMA temp_store = MEMORY');

// Create tables with proper structure
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    farm_name TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$db->exec("CREATE TABLE IF NOT EXISTS farms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    location TEXT,
    size_acres REAL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS livestock (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    farm_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    type TEXT NOT NULL,
    breed TEXT,
    count INTEGER DEFAULT 1,
    health TEXT,
    value REAL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS crops (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    farm_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    type TEXT,
    area REAL,
    planted_date DATE,
    expected_harvest DATE,
    status TEXT DEFAULT 'planted',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    farm_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    category TEXT,
    priority TEXT DEFAULT 'medium',
    due_date DATE,
    completed BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS finances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    farm_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    category TEXT,
    amount REAL NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    payment_method TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id)
)");

// Create indexes for better performance
$db->exec("CREATE INDEX IF NOT EXISTS idx_livestock_user_farm ON livestock(user_id, farm_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_crops_user_farm ON crops(user_id, farm_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_user_farm ON tasks(user_id, farm_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_finances_user_farm ON finances(user_id, farm_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_farms_user ON farms(user_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_completed ON tasks(completed)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_finances_date ON finances(date)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_tasks_due_date ON tasks(due_date)");

// Create default farm for existing users
$users = $db->query("SELECT id FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM farms)");
while ($user = $users->fetchArray(SQLITE3_ASSOC)) {
    $stmt = $db->prepare("INSERT INTO farms (user_id, name, location) VALUES (:user_id, :name, :location)");
    $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
    $stmt->bindValue(':name', 'Default Farm', SQLITE3_TEXT);
    $stmt->bindValue(':location', 'Not specified', SQLITE3_TEXT);
    $stmt->execute();
}

// Add farm_id columns to existing tables if they don't exist
$tables_to_update = [
    'livestock' => 'user_id INTEGER NOT NULL, farm_id INTEGER NOT NULL',
    'crops' => 'user_id INTEGER NOT NULL, farm_id INTEGER NOT NULL',
    'tasks' => 'user_id INTEGER NOT NULL, farm_id INTEGER NOT NULL',
    'finances' => 'user_id INTEGER NOT NULL, farm_id INTEGER NOT NULL'
];

foreach ($tables_to_update as $table => $columns) {
    // Check if farm_id column exists
    $columns_info = $db->query("PRAGMA table_info($table)");
    $has_farm_id = false;
    while ($column = $columns_info->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'farm_id') {
            $has_farm_id = true;
            break;
        }
    }
    
    // Add farm_id column if it doesn't exist
    if (!$has_farm_id) {
        $db->exec("ALTER TABLE $table ADD COLUMN farm_id INTEGER");
    }
}

// Update existing records to have farm_id
$default_farm = $db->prepare("SELECT id FROM farms WHERE user_id = :user_id LIMIT 1");

$tables = ['livestock', 'crops', 'tasks', 'finances'];
foreach ($tables as $table) {
    // Check if farm_id column exists
    $columns = $db->query("PRAGMA table_info($table)");
    $has_farm_id = false;
    while ($column = $columns->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'farm_id') {
            $has_farm_id = true;
            break;
        }
    }
    
    if ($has_farm_id) {
        $records = $db->query("SELECT id, user_id FROM $table WHERE farm_id IS NULL OR farm_id = 0");
        while ($record = $records->fetchArray(SQLITE3_ASSOC)) {
            $default_farm->bindValue(':user_id', $record['user_id'], SQLITE3_INTEGER);
            $farm_result = $default_farm->execute();
            $farm = $farm_result->fetchArray(SQLITE3_ASSOC);
            
            if ($farm) {
                $update_stmt = $db->prepare("UPDATE $table SET farm_id = :farm_id WHERE id = :id");
                $update_stmt->bindValue(':farm_id', $farm['id'], SQLITE3_INTEGER);
                $update_stmt->bindValue(':id', $record['id'], SQLITE3_INTEGER);
                $update_stmt->execute();
            }
        }
    }
}
?>
