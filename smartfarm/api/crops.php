<?php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit();
}

requireAuth();

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
    
    if ($action === 'add_crop') {
        $name = Security::sanitize($_POST['name'] ?? '', 'string');
        $type = Security::sanitize($_POST['type'] ?? '', 'string');
        $area = (float)($_POST['area'] ?? 0);
        $planted_date = Security::sanitize($_POST['planted_date'] ?? '', 'string');
        $expected_harvest = Security::sanitize($_POST['expected_harvest'] ?? '', 'string');
        $status = Security::sanitize($_POST['status'] ?? 'planted', 'string');
        
        if (empty($name) || empty($type)) {
            echo json_encode(['success' => false, 'error' => 'Name and type are required']);
            exit();
        }
        
        if ($area < 0.01 || $area > 999999) {
            echo json_encode(['success' => false, 'error' => 'Invalid area']);
            exit();
        }
        
        if (!Security::validate($planted_date, 'date')) {
            echo json_encode(['success' => false, 'error' => 'Invalid planted date']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO crops (user_id, farm_id, name, type, area, planted_date, expected_harvest, status) 
                              VALUES (:user_id, :farm_id, :name, :type, :area, :planted_date, :expected_harvest, :status)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':area', $area, SQLITE3_FLOAT);
        $stmt->bindValue(':planted_date', $planted_date, SQLITE3_TEXT);
        $stmt->bindValue(':expected_harvest', $expected_harvest, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->execute();
        
        $cropId = $db->lastInsertRowid();
        logAudit($db, 'CREATE', 'crops', $cropId, null, ['name' => $name, 'type' => $type]);
        
        echo json_encode(['success' => true, 'message' => 'Crop added successfully', 'id' => $cropId]);
        
    } elseif ($action === 'update_crop') {
        $id = (int)($_POST['id'] ?? 0);
        $name = Security::sanitize($_POST['name'] ?? '', 'string');
        $status = Security::sanitize($_POST['status'] ?? 'planted', 'string');
        
        if (empty($name) || $id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit();
        }
        
        $stmt = $db->prepare("UPDATE crops SET name = :name, status = :status, updated_at = CURRENT_TIMESTAMP 
                              WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->execute();
        
        logAudit($db, 'UPDATE', 'crops', $id);
        echo json_encode(['success' => true, 'message' => 'Crop updated successfully']);
        
    } elseif ($action === 'delete_crop') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            exit();
        }
        
        $stmt = $db->prepare("DELETE FROM crops WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        
        logAudit($db, 'DELETE', 'crops', $id);
        echo json_encode(['success' => true, 'message' => 'Crop deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    $logger->error('Crops API error', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
