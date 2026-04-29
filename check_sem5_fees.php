<?php
require_once 'core/db.php';
$stmt = $pdo->prepare("SELECT id FROM fee_structures WHERE semester_id = 5");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
