<?php
session_start();
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    $input = $_POST;
}

$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$role = trim($input['role'] ?? '');

if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

$auth = new Auth();

if ($auth->login($username, $password, $role)) {
    $redirect_url = ($role === 'student') ? 'dashboard.php' : 'dashboard1.php';
    echo json_encode(['success' => true, 'redirect' => $redirect_url]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials or role mismatch']);
}
?>
