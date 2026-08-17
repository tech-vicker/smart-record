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
    
    if ($action === 'add_finance') {
        $type = Security::sanitize($_POST['type'] ?? '', 'string');
        $category = Security::sanitize($_POST['category'] ?? '', 'string');
        $amount = (float)($_POST['amount'] ?? 0);
        $date = Security::sanitize($_POST['date'] ?? '', 'string');
        $description = Security::sanitize($_POST['description'] ?? '', 'string');
        $payment_method = Security::sanitize($_POST['payment_method'] ?? '', 'string');
        
        if (empty($type) || empty($category) || $amount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit();
        }
        
        if (!in_array($type, ['income', 'expense'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid type']);
            exit();
        }
        
        if (!Security::validate($date, 'date')) {
            echo json_encode(['success' => false, 'error' => 'Invalid date']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO finances (user_id, farm_id, type, category, amount, date, description, payment_method) 
                              VALUES (:user_id, :farm_id, :type, :category, :amount, :date, :description, :payment_method)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':type', $type, SQLITE3_TEXT);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $stmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':payment_method', $payment_method, SQLITE3_TEXT);
        $stmt->execute();
        
        $financeId = $db->lastInsertRowid();
        logAudit($db, 'CREATE', 'finances', $financeId, null, ['type' => $type, 'amount' => $amount]);
        
        echo json_encode(['success' => true, 'message' => 'Financial record added successfully', 'id' => $financeId]);
        
    } elseif ($action === 'update_finance') {
        $id = (int)($_POST['id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $category = Security::sanitize($_POST['category'] ?? '', 'string');
        
        if ($id < 1 || $amount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit();
        }
        
        $stmt = $db->prepare("UPDATE finances SET amount = :amount, category = :category, updated_at = CURRENT_TIMESTAMP 
                              WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':amount', $amount, SQLITE3_FLOAT);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $stmt->execute();
        
        logAudit($db, 'UPDATE', 'finances', $id);
        echo json_encode(['success' => true, 'message' => 'Financial record updated successfully']);
        
    } elseif ($action === 'delete_finance') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            exit();
        }
        
        $stmt = $db->prepare("DELETE FROM finances WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        
        logAudit($db, 'DELETE', 'finances', $id);
        echo json_encode(['success' => true, 'message' => 'Financial record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    $logger->error('Finances API error', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
