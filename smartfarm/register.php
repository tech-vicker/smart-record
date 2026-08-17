<?php
ini_set('display_errors', Config::isDebug() ? 1 : 0);
ini_set('display_startup_errors', Config::isDebug() ? 1 : 0);
error_reporting(Config::isDebug() ? E_ALL : 0);

require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = Security::sanitize($_POST['name'] ?? '', 'string');
        $email = Security::sanitize($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        
        // Validate input
        if (empty($name)) {
            $error = 'Name is required';
        } else if (!Security::validate($email, 'email')) {
            $error = 'Please enter a valid email address';
        } else if (!Security::validate($password, 'password')) {
            $minLength = Config::get('PASSWORD_MIN_LENGTH', 8);
            $error = "Password must be at least $minLength characters long and contain at least one uppercase letter and one number";
        } else if ($password !== $password_confirm) {
            $error = 'Passwords do not match';
        } else {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            if ($result->fetchArray()) {
                $error = 'Email already registered';
            } else {
                // Create new user
                $hashed_password = Security::hashPassword($password);
                $insert = $db->prepare("INSERT INTO users (name, email, password, farm_name) VALUES (:name, :email, :password, :farm_name)");
                $insert->bindValue(':name', $name, SQLITE3_TEXT);
                $insert->bindValue(':email', $email, SQLITE3_TEXT);
                $insert->bindValue(':password', $hashed_password, SQLITE3_TEXT);
                $insert->bindValue(':farm_name', $name . "'s Farm", SQLITE3_TEXT);
                $insert->execute();
                
                $userId = $db->lastInsertRowid();
                
                // Create default farm
                $farmStmt = $db->prepare("INSERT INTO farms (user_id, name, location) VALUES (:user_id, :name, :location)");
                $farmStmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
                $farmStmt->bindValue(':name', $name . "'s Farm", SQLITE3_TEXT);
                $farmStmt->bindValue(':location', 'Not specified', SQLITE3_TEXT);
                $farmStmt->execute();
                
                logAudit($db, 'REGISTER', 'users', $userId);
                $logger->info('New user registered', ['email' => $email]);
                
                $message = 'Registration successful! Please login with your credentials.';
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred during registration';
        $logger->error('Registration error', ['error' => $e->getMessage()]);
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
    <style>
        .auth-page { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .auth-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 1rem; }
        .auth-box { background: white; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 2rem; max-width: 400px; width: 100%; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h1 { color: #333; margin: 1rem 0; }
        .tagline { color: #666; font-size: 0.95rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #333; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #667eea; color: white; width: 100%; }
        .btn-primary:hover { background: #5568d3; }
        .error { background: #fee; color: #c33; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #c33; }
        .success { background: #efe; color: #3c3; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #3c3; }
        .auth-link { text-align: center; margin-top: 1.5rem; }
        .auth-link a { color: #667eea; text-decoration: none; }
        .auth-link a:hover { text-decoration: underline; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #667eea; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .password-requirements { font-size: 0.85rem; color: #666; margin-top: 0.5rem; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <a href="home.php" class="back-link">← Back to Home</a>
                <h1>🌾 SmartFarm</h1>
                <p class="tagline">Create Your Account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error"><?php echo Security::escape($error); ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="success"><?php echo Security::escape($message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a strong password">
                    <div class="password-requirements">
                        Must be at least 8 characters with 1 uppercase letter and 1 number
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required placeholder="Confirm your password">
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            
            <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
