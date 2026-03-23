<?php
session_start();

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
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
?>
