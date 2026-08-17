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
        $email = Security::sanitize($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        
        // Validate email format
        if (!Security::validate($email, 'email')) {
            $error = 'Please enter a valid email address';
            logLoginAttempt($db, $email, false);
        } else if (empty($password)) {
            $error = 'Password is required';
            logLoginAttempt($db, $email, false);
        } else if (isAccountLocked($db, $email)) {
            $error = 'Account is temporarily locked due to multiple failed login attempts. Please try again in 15 minutes.';
            logLoginAttempt($db, $email, false);
        } else {
            // Check login attempts
            $attempts = getLoginAttempts($db, $email);
            $maxAttempts = Config::get('MAX_LOGIN_ATTEMPTS', 5);
            
            if ($attempts >= $maxAttempts) {
                lockAccount($db, $email);
                $error = 'Account locked due to too many failed login attempts. Please try again in 15 minutes.';
                logLoginAttempt($db, $email, false);
            } else {
                // Try to authenticate
                $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindValue(':email', $email, SQLITE3_TEXT);
                $result = $stmt->execute();
                $user = $result->fetchArray(SQLITE3_ASSOC);
                
                if ($user && Security::verifyPassword($password, $user['password'])) {
                    // Successful login
                    $_SESSION['user_id'] = $user['id'];
                    
                    // Regenerate session ID for security
                    Security::regenerateSessionId();
                    
                    // Generate CSRF token
                    Security::generateCSRFToken();
                    
                    // Update last login
                    $updateStmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP, login_attempts = 0, locked_until = NULL WHERE id = :id");
                    $updateStmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
                    $updateStmt->execute();
                    
                    logLoginAttempt($db, $email, true);
                    logAudit($db, 'LOGIN', 'users', $user['id']);
                    
                    $logger->info('User logged in successfully', ['email' => $email]);
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // Failed login
                    $error = 'Invalid email or password';
                    logLoginAttempt($db, $email, false);
                    $logger->warning('Failed login attempt', ['email' => $email, 'ip' => Security::getUserIP()]);
                }
            }
        }
    } catch (Exception $e) {
        $error = 'An error occurred. Please try again.';
        $logger->error('Login error', ['error' => $e->getMessage()]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFarm - Login</title>
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
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #667eea; color: white; width: 100%; }
        .btn-primary:hover { background: #5568d3; }
        .btn-primary:disabled { background: #ccc; cursor: not-allowed; }
        .error { background: #fee; color: #c33; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #c33; }
        .success { background: #efe; color: #3c3; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; border-left: 4px solid #3c3; }
        .auth-link { text-align: center; margin-top: 1.5rem; }
        .auth-link a { color: #667eea; text-decoration: none; }
        .auth-link a:hover { text-decoration: underline; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #667eea; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <a href="home.php" class="back-link">← Back to Home</a>
                <h1>🌾 SmartFarm</h1>
                <p class="tagline">Welcome Back!</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error"><?php echo Security::escape($error); ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="success"><?php echo Security::escape($message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="your@email.com">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
            <p class="auth-link" style="font-size: 0.9rem; margin-top: 2rem; color: #999;">
                Demo Credentials:<br>
                Email: admin@smartfarm.com<br>
                Password: Admin123!
            </p>
        </div>
    </div>
</body>
</html>
