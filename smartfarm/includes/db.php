<?php
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../config/Logger.php';
require_once __DIR__ . '/../config/Security.php';
require_once __DIR__ . '/../config/Database.php';

// Initialize logger
$logger = new Logger();

// Get database instance
$db = Database::getInstance();

try {
    // Create tables with proper structure
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        farm_name TEXT,
        last_login DATETIME,
        login_attempts INTEGER DEFAULT 0,
        locked_until DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS farms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        location TEXT,
        size_acres REAL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
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
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
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
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
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
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
    )");

    // Create audit log table
    $db->exec("CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        action TEXT NOT NULL,
        table_name TEXT,
        record_id INTEGER,
        old_values TEXT,
        new_values TEXT,
        ip_address TEXT,
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // Create login attempts table
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        ip_address TEXT NOT NULL,
        success BOOLEAN DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create indexes for better performance
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_livestock_user_farm ON livestock(user_id, farm_id)",
        "CREATE INDEX IF NOT EXISTS idx_crops_user_farm ON crops(user_id, farm_id)",
        "CREATE INDEX IF NOT EXISTS idx_tasks_user_farm ON tasks(user_id, farm_id)",
        "CREATE INDEX IF NOT EXISTS idx_finances_user_farm ON finances(user_id, farm_id)",
        "CREATE INDEX IF NOT EXISTS idx_farms_user ON farms(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_tasks_completed ON tasks(completed)",
        "CREATE INDEX IF NOT EXISTS idx_finances_date ON finances(date)",
        "CREATE INDEX IF NOT EXISTS idx_tasks_due_date ON tasks(due_date)",
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_crops_status ON crops(status)",
        "CREATE INDEX IF NOT EXISTS idx_audit_logs_user ON audit_logs(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_login_attempts_email ON login_attempts(email)"
    ];

    foreach ($indexes as $index) {
        $db->exec($index);
    }

    // Create default farm for existing users
    $users = $db->query("SELECT id FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM farms)");
    if ($users) {
        while ($user = $users->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO farms (user_id, name, location) VALUES (:user_id, :name, :location)");
            $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':name', 'Default Farm', SQLITE3_TEXT);
            $stmt->bindValue(':location', 'Not specified', SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    // Create demo users
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    
    // Demo Admin
    $stmt->bindValue(':email', 'admin@smartfarm.com', SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result->fetchArray()) {
        $hashed_admin = Security::hashPassword('Admin123!');
        $insert = $db->prepare("INSERT INTO users (name, email, password, farm_name) VALUES (:name, :email, :password, :farm_name)");
        $insert->bindValue(':name', 'Admin User', SQLITE3_TEXT);
        $insert->bindValue(':email', 'admin@smartfarm.com', SQLITE3_TEXT);
        $insert->bindValue(':password', $hashed_admin, SQLITE3_TEXT);
        $insert->bindValue(':farm_name', 'Demo Farm HQ', SQLITE3_TEXT);
        $insert->execute();
        $logger->info('Demo admin user created', ['email' => 'admin@smartfarm.com']);
    }

    // Demo Test User
    $stmt->bindValue(':email', 'test@smartfarm.com', SQLITE3_TEXT);
    $result = $stmt->execute();
    if (!$result->fetchArray()) {
        $hashed_test = Security::hashPassword('Test123!');
        $insert = $db->prepare("INSERT INTO users (name, email, password, farm_name) VALUES (:name, :email, :password, :farm_name)");
        $insert->bindValue(':name', 'Test Farmer', SQLITE3_TEXT);
        $insert->bindValue(':email', 'test@smartfarm.com', SQLITE3_TEXT);
        $insert->bindValue(':password', $hashed_test, SQLITE3_TEXT);
        $insert->bindValue(':farm_name', 'Test Acres Farm', SQLITE3_TEXT);
        $insert->execute();
        $logger->info('Demo test user created', ['email' => 'test@smartfarm.com']);
    }

    // Create default farms for demos
    $demo_users = $db->query("SELECT id FROM users WHERE email IN ('admin@smartfarm.com', 'test@smartfarm.com') AND id NOT IN (SELECT DISTINCT user_id FROM farms)");
    if ($demo_users) {
        while ($user = $demo_users->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT INTO farms (user_id, name, location) VALUES (:user_id, :name, :location)");
            $stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':name', 'Demo Farm', SQLITE3_TEXT);
            $stmt->bindValue(':location', 'Demo Location', SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    $logger->info('Database initialization complete');

} catch (Exception $e) {
    $logger->critical('Database initialization failed', ['error' => $e->getMessage()]);
    if (Config::isDebug()) {
        die('Database Error: ' . $e->getMessage());
    } else {
        die('Database Error: Unable to initialize database. Please check logs.');
    }
}
