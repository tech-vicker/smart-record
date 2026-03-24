<?php
// Enable error reporting for deployment debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';


if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $farm_name = $_POST['farm_name'] ?? '';
    
    if ($name && $email && $password && $farm_name) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, farm_name) VALUES (:name, :email, :password, :farm_name)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
        $stmt->bindValue(':farm_name', $farm_name, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            $success = 'Registration successful! Please login.';
        } else {
            $error = 'Email already exists or registration failed';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFarm - Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <a href="home.php" class="back-link">← Back to Home</a>
                <h1>🌾 SmartFarm</h1>
                <p class="tagline">Create Your Farm Account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?> <a href="login.php">Login now</a></div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="farm_name">Farm Name</label>
                        <input type="text" id="farm_name" name="farm_name" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            <?php endif; ?>
            
            <p class="auth-link">Already have an account? <a href="index.php">Login</a></p>
        </div>
    </div>
</body>
</html>
