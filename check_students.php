<?php
require_once 'core/db.php';
$stmt = $pdo->query("SELECT u.id, u.name, u.email, p.name as program, s.number as semester 
                     FROM users u 
                     LEFT JOIN programs p ON u.program_id = p.id 
                     LEFT JOIN semesters s ON u.semester_id = s.id 
                     WHERE u.role = 'student'");
$students = $stmt->fetchAll();

if (empty($students)) {
    echo "INFO: No students found in the system.";
} else {
    echo "Student ID | Name | Program | Semester\n";
    echo "--------------------------------------\n";
    foreach ($students as $s) {
        echo $s['id'] . " | " . $s['name'] . " | " . ($s['program'] ?: 'N/A') . " | " . ($s['semester'] ?: 'N/A') . "\n";
    }
}
?>
