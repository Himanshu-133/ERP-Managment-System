<?php
require_once 'auth.php';
require_once 'student.php';

$auth = new Auth();
$auth->requireRole('student');

$student = new Student();
$message = '';
$messageType = '';

// Handle form submission
if ($_POST) {
    $data = [
        'name' => trim($_POST['name']),
        'roll_number' => trim($_POST['roll_number']),
        'email' => trim($_POST['email']),
        'department' => trim($_POST['department']),
        'course' => trim($_POST['course']),
        'phone' => trim($_POST['phone']),
        'address' => trim($_POST['address'])
    ];
    
    if ($student->updateStudentDetails($_SESSION['user_id'], $data)) {
        $message = 'Details updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error updating details. Please try again.';
        $messageType = 'error';
    }
}

// Get current student details
$studentDetails = $student->getStudentDetails($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - ERP System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="navbar">
            <div class="navbar-content">
                <h1>Student Dashboard</h1>
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
                    <h2>My Details</h2>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="student-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" name="name" id="name" 
                                       value="<?php echo htmlspecialchars($studentDetails['name'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="roll_number">Roll Number *</label>
                                <input type="text" name="roll_number" id="roll_number" 
                                       value="<?php echo htmlspecialchars($studentDetails['roll_number'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" name="email" id="email" 
                                       value="<?php echo htmlspecialchars($studentDetails['email'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" name="phone" id="phone" 
                                       value="<?php echo htmlspecialchars($studentDetails['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="department">Department *</label>
                                <select name="department" id="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science" <?php echo ($studentDetails['department'] ?? '') === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Information Technology" <?php echo ($studentDetails['department'] ?? '') === 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                    <option value="Electronics" <?php echo ($studentDetails['department'] ?? '') === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                    <option value="Mechanical" <?php echo ($studentDetails['department'] ?? '') === 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                                    <option value="Civil" <?php echo ($studentDetails['department'] ?? '') === 'Civil' ? 'selected' : ''; ?>>Civil</option>
                                    <option value="Electrical" <?php echo ($studentDetails['department'] ?? '') === 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="course">Course *</label>
                                <select name="course" id="course" required>
                                    <option value="">Select Course</option>
                                    <option value="B.Tech" <?php echo ($studentDetails['course'] ?? '') === 'B.Tech' ? 'selected' : ''; ?>>B.Tech</option>
                                    <option value="B.E" <?php echo ($studentDetails['course'] ?? '') === 'B.E' ? 'selected' : ''; ?>>B.E</option>
                                    <option value="M.Tech" <?php echo ($studentDetails['course'] ?? '') === 'M.Tech' ? 'selected' : ''; ?>>M.Tech</option>
                                    <option value="M.E" <?php echo ($studentDetails['course'] ?? '') === 'M.E' ? 'selected' : ''; ?>>M.E</option>
                                    <option value="PhD" <?php echo ($studentDetails['course'] ?? '') === 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" rows="4"><?php echo htmlspecialchars($studentDetails['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $studentDetails ? 'Update Details' : 'Save Details'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($studentDetails): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Current Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Name:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['name']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Roll Number:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['roll_number']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Phone:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['phone'] ?: 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Department:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['department']); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Course:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['course']); ?></span>
                        </div>
                        <div class="info-item full-width">
                            <strong>Address:</strong>
                            <span><?php echo htmlspecialchars($studentDetails['address'] ?: 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Last Updated:</strong>
                            <span><?php echo date('M d, Y H:i', strtotime($studentDetails['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="main.js"></script>
</body>
</html>
