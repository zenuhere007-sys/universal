<?php
require_once 'core/db.php';
$stmt = $pdo->query("SELECT COUNT(*) FROM invoices WHERE semester_id = 5");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
