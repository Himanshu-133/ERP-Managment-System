<?php
require_once 'auth.php';
require_once 'student.php';

$auth = new Auth();
$auth->requireRole('teacher');

$student = new Student();
$students = $student->getAllStudents();

$viewStudent = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    foreach ($students as $s) {
        if ($s['id'] == $_GET['id']) {
            $viewStudent = $s;
            break;
        }
    }
}

if (!$viewStudent) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - ERP System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="navbar">
            <div class="navbar-content">
                <h1>Student Details</h1>
                <div class="navbar-right">
                    <div class="user-info">
                        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </nav>
        
        <div class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2><?php echo htmlspecialchars($viewStudent['name']); ?> - Complete Profile</h2>
                </div>
                <div class="card-body">
                    <div class="student-profile">
                        <div class="profile-section">
                            <h3>Personal Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <strong>Full Name:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Roll Number:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['roll_number']); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Email Address:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Phone Number:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['phone'] ?: 'Not provided'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <h3>Academic Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <strong>Department:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['department']); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Course:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['course']); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Username:</strong>
                                    <span><?php echo htmlspecialchars($viewStudent['username']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <h3>Address Information</h3>
                            <div class="info-item full-width">
                                <strong>Address:</strong>
                                <span><?php echo htmlspecialchars($viewStudent['address'] ?: 'Not provided'); ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-section">
                            <h3>System Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <strong>Record Created:</strong>
                                    <span><?php echo date('M d, Y H:i', strtotime($viewStudent['created_at'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong>Last Updated:</strong>
                                    <span><?php echo date('M d, Y H:i', strtotime($viewStudent['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="dashboard.php?edit=<?php echo $viewStudent['id']; ?>" class="btn btn-warning">Edit Details</a>
                        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="main.js"></script>
</body>
</html>
