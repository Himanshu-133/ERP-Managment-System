<?php
require_once 'auth.php';
require_once 'student.php';

$auth = new Auth();
$auth->requireRole('teacher');

$student = new Student();
$rows = $student->getAllStudents();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['id','user_id','username','name','roll_number','email','department','course','phone','address','created_at','updated_at']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'] ?? '',
        $r['user_id'] ?? '',
        $r['username'] ?? '',
        $r['name'] ?? '',
        $r['roll_number'] ?? '',
        $r['email'] ?? '',
        $r['department'] ?? '',
        $r['course'] ?? '',
        $r['phone'] ?? '',
        $r['address'] ?? '',
        $r['created_at'] ?? '',
        $r['updated_at'] ?? ''
    ]);
}
fclose($out);
exit();
