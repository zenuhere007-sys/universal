<?php
require_once 'core/db.php';
$stmt = $pdo->query("SELECT id FROM invoices ORDER BY id DESC LIMIT 2");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
