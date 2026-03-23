<?php
require_once 'includes/auth.php';
requireAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$farm_id = $data['farm_id'] ?? null;

if (!$farm_id) {
    echo json_encode(['success' => false, 'error' => 'Farm ID required']);
    exit();
}

$user_id = getUserId();

// Verify farm belongs to user
$stmt = $db->prepare("SELECT id FROM farms WHERE id = :id AND user_id = :user_id");
$stmt->bindValue(':id', $farm_id, SQLITE3_INTEGER);
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$farm = $result->fetchArray(SQLITE3_ASSOC);

if (!$farm) {
    echo json_encode(['success' => false, 'error' => 'Farm not found']);
    exit();
}

// Set session farm_id
$_SESSION['farm_id'] = $farm_id;

echo json_encode(['success' => true]);
?>
