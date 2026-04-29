<?php
require_once 'db.php';
require_once 'session.php';

header('Content-Type: application/json');

// Security: Only staff can search
if (!in_array($_SESSION['role'], ['super_admin', 'finance', 'clerk'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$type = $_GET['type'] ?? 'student'; // 'student' or 'invoice'
$program_id = $_GET['program_id'] ?? '';
$semester_id = $_GET['semester_id'] ?? '';
$query = trim($_GET['query'] ?? '');

$params = [];
$where = ["1=1"];

if ($program_id) {
    $where[] = "u.program_id = ?";
    $params[] = $program_id;
}

if ($semester_id && $type === 'invoice') {
    $where[] = "i.semester_id = ?";
    $params[] = $semester_id;
}

if ($query) {
    $where[] = "(u.name LIKE ? OR u.roll_no LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if ($type === 'student') {
    $sql = "SELECT u.id, u.name, u.roll_no, p.name as program_name 
            FROM users u 
            LEFT JOIN programs p ON u.program_id = p.id 
            WHERE " . implode(" AND ", $where) . " AND u.role = 'student' 
            ORDER BY u.name LIMIT 50";
} else {
    // Search Invoices for Installments
    $sql = "SELECT i.id, i.payable_amount, u.name, u.roll_no 
            FROM invoices i 
            JOIN users u ON i.user_id = u.id 
            WHERE " . implode(" AND ", $where) . " AND i.status != 'paid' 
            ORDER BY u.name LIMIT 50";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
