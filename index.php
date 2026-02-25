<?php
session_start();
require_once 'auth.php';

$auth = new Auth();
$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password']; 
    $role = $_POST['role']; 
     
    if ($auth->login($username, $password, $role)) { 
        if ($role === 'student') {
            header("Location: dashboard.php");
        } else {
            header("Location: dashboard1.php");
        }
        exit();
    } else {
        $error = 'Invalid credentials or role mismatch';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Management System - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>ERP Management System</h1>
                <p>Please sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="role">Login as:</label>
                    <select name="role" id="role" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                
                <button type="submit" class="login-btn">Sign In</button>
            </form>
            
            <div class="demo-credentials">
                <h3>Demo Credentials:</h3>
                <p><strong>Student:</strong> username: student1, password: password</p>
                <p><strong>Teacher:</strong> username: teacher1, password: password</p>
            </div>
        </div>
    </div>
</body>
</html>
