<?php
require_once 'core/db.php';
$stmt = $pdo->prepare("SELECT i.* FROM invoices i JOIN users u ON i.user_id = u.id WHERE u.name = 'Zeenat'");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
