<?php
require_once 'auth.php';
require_once 'student.php';

$auth = new Auth();
$auth->requireRole('teacher');

$student = new Student();
$message = '';
$messageType = '';

// Handle edit form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $data = [
        'name' => trim($_POST['name']),
        'roll_number' => trim($_POST['roll_number']),
        'email' => trim($_POST['email']),
        'department' => trim($_POST['department']),
        'course' => trim($_POST['course']),
        'phone' => trim($_POST['phone']),
        'address' => trim($_POST['address'])
    ];
    
    if ($student->updateStudentById($_POST['student_id'], $data)) {
        $message = 'Student details updated successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error updating student details. Please try again.';
        $messageType = 'error';
    }
}

            // Handle add student action (teacher creates a new student account and details)
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) && $_POST['action'] === 'add')) {
                $newUsername = trim($_POST['new_username'] ?? '');
                $newPassword = trim($_POST['new_password'] ?? '');
                $newName = trim($_POST['new_name'] ?? '');
                $newRoll = trim($_POST['new_roll'] ?? '');
                $newEmail = trim($_POST['new_email'] ?? '');
                $newDepartment = trim($_POST['new_department'] ?? '');
                $newCourse = trim($_POST['new_course'] ?? '');
                $newPhone = trim($_POST['new_phone'] ?? '');
                $newAddress = trim($_POST['new_address'] ?? '');

                // Basic validation
                if ($newUsername === '' || $newPassword === '' || $newName === '' || $newRoll === '') {
                    $message = 'Username, password, name and roll number are required';
                    $messageType = 'error';
                } else {
                    try {
                        $db = new Database();
                        $conn = $db->getConnection();

                        // check username exists
                        $chk = $conn->prepare('SELECT id FROM users WHERE username = :u');
                        $chk->execute([':u' => $newUsername]);
                        if ($chk->fetch()) {
                            $message = 'Username already exists';
                            $messageType = 'error';
                        } else {
                            // check roll_number uniqueness
                            $chk2 = $conn->prepare('SELECT id FROM student_details WHERE roll_number = :r');
                            $chk2->execute([':r' => $newRoll]);
                            if ($chk2->fetch()) {
                                $message = 'Roll number already exists';
                                $messageType = 'error';
                            } else {
                                $hp = password_hash($newPassword, PASSWORD_DEFAULT);
                                $ins = $conn->prepare('INSERT INTO users (username,password,role) VALUES (:u,:p,:r)');
                                $ins->execute([':u' => $newUsername, ':p' => $hp, ':r' => 'student']);
                                $newUserId = (int)$conn->lastInsertId();

                                // Insert student details
                                $studentData = [
                                    'name' => $newName,
                                    'roll_number' => $newRoll,
                                    'email' => $newEmail,
                                    'department' => $newDepartment,
                                    'course' => $newCourse,
                                    'phone' => $newPhone,
                                    'address' => $newAddress
                                ];

                                if ($student->createStudent($newUserId, $studentData)) {
                                    $message = 'Student created successfully';
                                    $messageType = 'success';
                                    // refresh students list
                                    $students = $student->getAllStudents();
                                } else {
                                    $message = 'Student creation failed';
                                    $messageType = 'error';
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $message = 'Error: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            }

// Handle delete student action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    if ($delId > 0) {
        try {
            if ($student->deleteStudentById($delId)) {
                $message = 'Student removed successfully';
                $messageType = 'success';
                // refresh list
                $students = $student->getAllStudents();
            } else {
                $message = 'Failed to remove student';
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Invalid student id';
        $messageType = 'error';
    }
}

// Get all students
$students = $student->getAllStudents();

// Get student for editing if requested
$editStudent = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    foreach ($students as $s) {
        if ($s['id'] == $_GET['edit']) {
            $editStudent = $s;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - ERP System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="navbar">
            <div class="navbar-content">
                <h1>Teacher Dashboard</h1>
                <div class="navbar-right">
                    <div class="user-info">
                        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </nav>
        
        <div class="main-content">
            <?php if ($message): ?>
                <div class="<?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Add Student Form -->
            <div class="card">
                <div class="card-header">
                    <h2>Add New Student</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="add-student-form">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_username">Username *</label>
                                <input type="text" name="new_username" id="new_username" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Password *</label>
                                <input type="password" name="new_password" id="new_password" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_name">Full Name *</label>
                                <input type="text" name="new_name" id="new_name" required>
                            </div>
                            <div class="form-group">
                                <label for="new_roll">Roll Number *</label>
                                <input type="text" name="new_roll" id="new_roll" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_email">Email</label>
                                <input type="email" name="new_email" id="new_email">
                            </div>
                            <div class="form-group">
                                <label for="new_phone">Phone</label>
                                <input type="tel" name="new_phone" id="new_phone">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_department">Department</label>
                                <input type="text" name="new_department" id="new_department">
                            </div>
                            <div class="form-group">
                                <label for="new_course">Course</label>
                                <input type="text" name="new_course" id="new_course">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_address">Address</label>
                            <textarea name="new_address" id="new_address" rows="2"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Create Student</button>
                            <a href="export_students.php" class="btn btn-secondary" style="margin-left:12px;">Export CSV</a>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            (function(){
                const form = document.querySelector('.add-student-form');
                if (!form) return;
                form.addEventListener('submit', function(e){
                    const username = form.querySelector('#new_username').value.trim();
                    const password = form.querySelector('#new_password').value.trim();
                    const name = form.querySelector('#new_name').value.trim();
                    const roll = form.querySelector('#new_roll').value.trim();
                    const email = form.querySelector('#new_email').value.trim();
                    if (!username || !password || !name || !roll) {
                        alert('Please fill username, password, name and roll number');
                        e.preventDefault();
                        return;
                    }
                    if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                        alert('Please enter a valid email address');
                        e.preventDefault();
                        return;
                    }
                });
            })();
            </script>
            
            <?php if ($editStudent): ?>
            <!-- Edit Student Form -->
            <div class="card">
                <div class="card-header">
                    <h2>Edit Student Details</h2>
                </div>
                <div class="card-body">
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="student_id" value="<?php echo $editStudent['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" name="name" id="name" 
                                       value="<?php echo htmlspecialchars($editStudent['name']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="roll_number">Roll Number *</label>
                                <input type="text" name="roll_number" id="roll_number" 
                                       value="<?php echo htmlspecialchars($editStudent['roll_number']); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" name="email" id="email" 
                                       value="<?php echo htmlspecialchars($editStudent['email']); ?>" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" name="phone" id="phone" 
                                       value="<?php echo htmlspecialchars($editStudent['phone']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="department">Department *</label>
                                <select name="department" id="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science" <?php echo $editStudent['department'] === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Information Technology" <?php echo $editStudent['department'] === 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                                    <option value="Electronics" <?php echo $editStudent['department'] === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                                    <option value="Mechanical" <?php echo $editStudent['department'] === 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                                    <option value="Civil" <?php echo $editStudent['department'] === 'Civil' ? 'selected' : ''; ?>>Civil</option>
                                    <option value="Electrical" <?php echo $editStudent['department'] === 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="course">Course *</label>
                                <select name="course" id="course" required>
                                    <option value="">Select Course</option>
                                    <option value="B.Tech" <?php echo $editStudent['course'] === 'B.Tech' ? 'selected' : ''; ?>>B.Tech</option>
                                    <option value="B.E" <?php echo $editStudent['course'] === 'B.E' ? 'selected' : ''; ?>>B.E</option>
                                    <option value="M.Tech" <?php echo $editStudent['course'] === 'M.Tech' ? 'selected' : ''; ?>>M.Tech</option>
                                    <option value="M.E" <?php echo $editStudent['course'] === 'M.E' ? 'selected' : ''; ?>>M.E</option>
                                    <option value="PhD" <?php echo $editStudent['course'] === 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" rows="4"><?php echo htmlspecialchars($editStudent['address']); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Update Student</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Students List -->
            <div class="card">
                <div class="card-header">
                    <h2>All Students (<?php echo count($students); ?>)</h2>
                </div>
                <div class="card-body">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Search students by name, roll number, department...">
                    </div>
                    
                    <?php if (empty($students)): ?>
                        <div class="no-data">
                            <p>No student records found.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Roll Number</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Course</th>
                                        <th>Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $s): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><?php echo htmlspecialchars($s['roll_number']); ?></td>
                                        <td><?php echo htmlspecialchars($s['email']); ?></td>
                                        <td><?php echo htmlspecialchars($s['department']); ?></td>
                                        <td><?php echo htmlspecialchars($s['course']); ?></td>
                                        <td><?php echo htmlspecialchars($s['phone'] ?: 'N/A'); ?></td>
                                        <td>
                                            <a href="dashboard.php?edit=<?php echo $s['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="view_student.php?id=<?php echo $s['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                            <form method="POST" style="display:inline-block;margin-left:6px;" onsubmit="return confirmDelete();">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Statistics Card -->
            <div class="card">
                <div class="card-header">
                    <h2>Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <?php
                        $departmentStats = [];
                        $courseStats = [];
                        
                        foreach ($students as $s) {
                            $departmentStats[$s['department']] = ($departmentStats[$s['department']] ?? 0) + 1;
                            $courseStats[$s['course']] = ($courseStats[$s['course']] ?? 0) + 1;
                        }
                        ?>
                        
                        <div class="stat-card">
                            <h3>Total Students</h3>
                            <div class="stat-number"><?php echo count($students); ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Departments</h3>
                            <div class="stat-number"><?php echo count($departmentStats); ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Courses</h3>
                            <div class="stat-number"><?php echo count($courseStats); ?></div>
                        </div>
                        
                        <div class="stat-card">
                            <h3>Most Popular Department</h3>
                            <div class="stat-text">
                                <?php 
                                if (!empty($departmentStats)) {
                                    $maxDept = array_keys($departmentStats, max($departmentStats))[0];
                                    echo htmlspecialchars($maxDept) . ' (' . $departmentStats[$maxDept] . ')';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($departmentStats)): ?>
                    <div class="department-breakdown">
                        <h4>Department Breakdown:</h4>
                        <div class="breakdown-list">
                            <?php foreach ($departmentStats as $dept => $count): ?>
                                <div class="breakdown-item">
                                    <span class="dept-name"><?php echo htmlspecialchars($dept); ?></span>
                                    <span class="dept-count"><?php echo $count; ?> students</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="main.js"></script>
    <script>
    function confirmDelete() {
        return confirm('Are you sure you want to permanently delete this student and their account? This action cannot be undone.');
    }
    </script>
</body>
</html>
