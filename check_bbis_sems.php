<?php
require_once 'core/db.php';
$stmt = $pdo->query("SELECT id, number FROM semesters WHERE program_id = 8");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
