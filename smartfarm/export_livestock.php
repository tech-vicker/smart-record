<?php
require_once 'includes/auth.php';
requireAuth();

header('Content-Type: application/json');

$user_id = getUserId();

try {
    // Get livestock data
    $stmt = $db->prepare("SELECT name, type, breed, count, health, value FROM livestock WHERE user_id = :user_id ORDER BY name");
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
