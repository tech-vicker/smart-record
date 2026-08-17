<?php
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../config/Logger.php';
require_once __DIR__ . '/../config/Security.php';

$logger = new Logger();

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
    if (!isset($_SESSION['user_id'])) {
        return ['name' => 'User', 'farm_name' => 'Farm', 'email' => ''];
    }
    
    try {
        $stmt = $db->prepare("SELECT id, name, email, farm_name FROM users WHERE id = :id");
        $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);
        return $user ?: ['name' => 'User', 'farm_name' => 'Farm', 'email' => ''];
    } catch (Exception $e) {
        $logger->error('Error fetching current user', ['error' => $e->getMessage()]);
        return ['name' => 'User', 'farm_name' => 'Farm', 'email' => ''];
    }
}

function getUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getCurrentFarm($db) {
    if (!isset($_SESSION['user_id'])) return null;
    
    try {
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
    } catch (Exception $e) {
        $logger->error('Error fetching current farm', ['error' => $e->getMessage()]);
        return null;
    }
}

function getFarms($db) {
    if (!isset($_SESSION['user_id'])) return [];
    
    try {
        $stmt = $db->prepare("SELECT * FROM farms WHERE user_id = :user_id ORDER BY name");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $farms = [];
        while ($farm = $result->fetchArray(SQLITE3_ASSOC)) {
            $farms[] = $farm;
        }
        
        return $farms;
    } catch (Exception $e) {
        $logger->error('Error fetching farms', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Log login attempt
 */
function logLoginAttempt($db, $email, $success = false) {
    try {
        $ip = Security::getUserIP();
        $stmt = $db->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (:email, :ip, :success)");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':success', $success ? 1 : 0, SQLITE3_INTEGER);
        $stmt->execute();
    } catch (Exception $e) {
        $logger->error('Error logging login attempt', ['error' => $e->getMessage()]);
    }
}

/**
 * Check if account is locked
 */
function isAccountLocked($db, $email) {
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND locked_until > datetime('now')");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC) !== false;
    } catch (Exception $e) {
        $logger->error('Error checking account lock', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Check login attempts
 */
function getLoginAttempts($db, $email) {
    try {
        $timeLimit = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $stmt = $db->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE email = :email AND success = 0 AND created_at > :time");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':time', $timeLimit, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row['attempts'] ?? 0;
    } catch (Exception $e) {
        $logger->error('Error getting login attempts', ['error' => $e->getMessage()]);
        return 0;
    }
}

/**
 * Lock account
 */
function lockAccount($db, $email) {
    try {
        $lockoutDuration = Config::get('LOCKOUT_DURATION', 900); // 15 minutes default
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+$lockoutDuration seconds"));
        
        $stmt = $db->prepare("UPDATE users SET locked_until = :locked_until WHERE email = :email");
        $stmt->bindValue(':locked_until', $lockedUntil, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->execute();
        
        $logger->warning('Account locked due to failed login attempts', ['email' => $email]);
    } catch (Exception $e) {
        $logger->error('Error locking account', ['error' => $e->getMessage()]);
    }
}

/**
 * Log audit action
 */
function logAudit($db, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    if (!isset($_SESSION['user_id'])) return;
    
    try {
        $ip = Security::getUserIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) 
                              VALUES (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip, :user_agent)");
        $stmt->bindValue(':user_id', $_SESSION['user_id'], SQLITE3_INTEGER);
        $stmt->bindValue(':action', $action, SQLITE3_TEXT);
        $stmt->bindValue(':table_name', $tableName, SQLITE3_TEXT);
        $stmt->bindValue(':record_id', $recordId, SQLITE3_INTEGER);
        $stmt->bindValue(':old_values', $oldValues ? json_encode($oldValues) : null, SQLITE3_TEXT);
        $stmt->bindValue(':new_values', $newValues ? json_encode($newValues) : null, SQLITE3_TEXT);
        $stmt->bindValue(':ip', $ip, SQLITE3_TEXT);
        $stmt->bindValue(':user_agent', $userAgent, SQLITE3_TEXT);
        $stmt->execute();
    } catch (Exception $e) {
        $logger->error('Error logging audit', ['error' => $e->getMessage()]);
    }
}
