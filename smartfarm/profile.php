<?php
require_once 'includes/header.php';
requireAuth();

$user = getCurrentUser($db);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $farm_name = $_POST['farm_name'];
        
        $stmt = $db->prepare("UPDATE users SET name = :name, farm_name = :farm_name WHERE id = :id");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':farm_name', $farm_name, SQLITE3_TEXT);
        $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $message = 'Profile updated successfully';
            $user = getCurrentUser($db);
        } else {
            $error = 'Failed to update profile';
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!empty($user['password']) && password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->bindValue(':password', $hashed, SQLITE3_TEXT);
                $stmt->bindValue(':id', $_SESSION['user_id'], SQLITE3_INTEGER);
                
                if ($stmt->execute()) {
                    $message = 'Password changed successfully';
                } else {
                    $error = 'Failed to change password';
                }
            } else {
                $error = 'New passwords do not match';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}
?>

<div class="page-header">
    <h1>👤 Profile Settings</h1>
</div>

<?php if ($message): ?>
    <div class="success"><?php echo $message; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <div class="profile-info">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        
        <form method="POST" class="profile-form">
            <h4>Edit Profile</h4>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Farm Name</label>
                <input type="text" name="farm_name" value="<?php echo htmlspecialchars($user['farm_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                <small>Email cannot be changed</small>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <div class="profile-card">
        <form method="POST" class="profile-form">
            <h4>Change Password</h4>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</div>

</main>
<script src="js/app.js"></script>
</body>
</html>
