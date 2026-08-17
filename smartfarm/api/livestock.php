<?php
require_once 'includes/header.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit();
}

requireAuth();

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
    logAudit($db, 'CSRF_TOKEN_VERIFICATION_FAILED', 'users', getUserId());
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security token verification failed']);
    exit();
}

$user_id = getUserId();
$current_farm = getCurrentFarm($db);
$farm_id = $current_farm ? $current_farm['id'] : 0;

if (!$farm_id) {
    echo json_encode(['success' => false, 'error' => 'No farm selected']);
    exit();
}

try {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_livestock') {
        // Validate and sanitize input
        $name = Security::sanitize($_POST['name'] ?? '', 'string');
        $type = Security::sanitize($_POST['type'] ?? '', 'string');
        $breed = Security::sanitize($_POST['breed'] ?? '', 'string');
        $count = (int)($_POST['count'] ?? 1);
        $health = Security::sanitize($_POST['health'] ?? 'good', 'string');
        $value = (float)($_POST['value'] ?? 0);
        
        // Validate required fields
        if (empty($name) || empty($type)) {
            echo json_encode(['success' => false, 'error' => 'Name and type are required']);
            exit();
        }
        
        if ($count < 1 || $count > 100000) {
            echo json_encode(['success' => false, 'error' => 'Invalid count']);
            exit();
        }
        
        if ($value < 0 || $value > 999999999) {
            echo json_encode(['success' => false, 'error' => 'Invalid value']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO livestock (user_id, farm_id, name, type, breed, count, health, value) 
                              VALUES (:user_id, :farm_id, :name, :type, :breed, :count, :health, :value)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':breed', $breed, SQLITE3_TEXT);
        $stmt->bindValue(':count', $count, SQLITE3_INTEGER);
        $stmt->bindValue(':health', $health, SQLITE3_TEXT);
        $stmt->bindValue(':value', $value, SQLITE3_FLOAT);
        $stmt->execute();
        
        $livestockId = $db->lastInsertRowid();
        logAudit($db, 'CREATE', 'livestock', $livestockId, null, ['name' => $name, 'type' => $type]);
        
        echo json_encode(['success' => true, 'message' => 'Livestock added successfully', 'id' => $livestockId]);
        
    } elseif ($action === 'update_livestock') {
        $id = (int)($_POST['id'] ?? 0);
        $name = Security::sanitize($_POST['name'] ?? '', 'string');
        $type = Security::sanitize($_POST['type'] ?? '', 'string');
        $breed = Security::sanitize($_POST['breed'] ?? '', 'string');
        $count = (int)($_POST['count'] ?? 1);
        $health = Security::sanitize($_POST['health'] ?? 'good', 'string');
        $value = (float)($_POST['value'] ?? 0);
        
        if (empty($name) || empty($type) || $id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit();
        }
        
        $stmt = $db->prepare("UPDATE livestock SET name = :name, type = :type, breed = :breed, count = :count, health = :health, value = :value, updated_at = CURRENT_TIMESTAMP 
                              WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':breed', $breed, SQLITE3_TEXT);
        $stmt->bindValue(':count', $count, SQLITE3_INTEGER);
        $stmt->bindValue(':health', $health, SQLITE3_TEXT);
        $stmt->bindValue(':value', $value, SQLITE3_FLOAT);
        $stmt->execute();
        
        logAudit($db, 'UPDATE', 'livestock', $id, null, ['name' => $name, 'type' => $type]);
        echo json_encode(['success' => true, 'message' => 'Livestock updated successfully']);
        
    } elseif ($action === 'delete_livestock') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            exit();
        }
        
        $stmt = $db->prepare("DELETE FROM livestock WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        
        logAudit($db, 'DELETE', 'livestock', $id);
        echo json_encode(['success' => true, 'message' => 'Livestock deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    $logger->error('Livestock API error', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . ($logger->isDebug() ? $e->getMessage() : 'Please try again')]);
}
