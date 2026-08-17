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
    
    if ($action === 'add_task') {
        $title = Security::sanitize($_POST['title'] ?? '', 'string');
        $category = Security::sanitize($_POST['category'] ?? '', 'string');
        $priority = Security::sanitize($_POST['priority'] ?? 'medium', 'string');
        $due_date = Security::sanitize($_POST['due_date'] ?? '', 'string');
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'error' => 'Title is required']);
            exit();
        }
        
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid priority']);
            exit();
        }
        
        if (!Security::validate($due_date, 'date')) {
            echo json_encode(['success' => false, 'error' => 'Invalid date']);
            exit();
        }
        
        $stmt = $db->prepare("INSERT INTO tasks (user_id, farm_id, title, category, priority, due_date) 
                              VALUES (:user_id, :farm_id, :title, :category, :priority, :due_date)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':title', $title, SQLITE3_TEXT);
        $stmt->bindValue(':category', $category, SQLITE3_TEXT);
        $stmt->bindValue(':priority', $priority, SQLITE3_TEXT);
        $stmt->bindValue(':due_date', $due_date, SQLITE3_TEXT);
        $stmt->execute();
        
        $taskId = $db->lastInsertRowid();
        logAudit($db, 'CREATE', 'tasks', $taskId, null, ['title' => $title]);
        
        echo json_encode(['success' => true, 'message' => 'Task added successfully', 'id' => $taskId]);
        
    } elseif ($action === 'update_task') {
        $id = (int)($_POST['id'] ?? 0);
        $completed = isset($_POST['completed']) ? 1 : 0;
        
        if ($id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            exit();
        }
        
        $stmt = $db->prepare("UPDATE tasks SET completed = :completed, updated_at = CURRENT_TIMESTAMP 
                              WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->bindValue(':completed', $completed, SQLITE3_INTEGER);
        $stmt->execute();
        
        logAudit($db, 'UPDATE', 'tasks', $id, null, ['completed' => $completed]);
        echo json_encode(['success' => true, 'message' => 'Task updated successfully']);
        
    } elseif ($action === 'delete_task') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid ID']);
            exit();
        }
        
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :user_id AND farm_id = :farm_id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':farm_id', $farm_id, SQLITE3_INTEGER);
        $stmt->execute();
        
        logAudit($db, 'DELETE', 'tasks', $id);
        echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    $logger->error('Tasks API error', ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'error' => 'An error occurred']);
}
