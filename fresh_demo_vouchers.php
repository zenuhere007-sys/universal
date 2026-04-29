<?php
require_once 'core/db.php';

try {
    // 1. Delete old invoices for Zeenat and Sawiba
    $pdo->exec("DELETE FROM invoices WHERE user_id IN (23, 24)");
    echo "Old invoices for Zeenat & Sawiba deleted.\n";

    // 2. Trigger fresh generation for BBIS Semester 8 (Semester ID 5)
    $semester_id = 5; 
    $stuStmt = $pdo->prepare("SELECT id, name, fee_category, scholarship_percent, scholarship_fixed FROM users WHERE id IN (23, 24)");
    $stuStmt->execute();
    $students = $stuStmt->fetchAll();

    $feeStmt = $pdo->prepare("SELECT * FROM fee_structures WHERE semester_id = ?");
    $feeStmt->execute([$semester_id]);
    $raw_fees = $feeStmt->fetchAll();
    $fees_by_cat = [];
    foreach($raw_fees as $rf) { $fees_by_cat[$rf['fee_category']] = $rf; }

    $count = 0;
    foreach($students as $s) {
        $cat = $s['fee_category'] ?: 'Regular';
        if (!isset($fees_by_cat[$cat])) continue;

        $fs = $fees_by_cat[$cat];
        // Note: Adding admission fee only if it's Semester 1, 
        // but since we are doing a demo, let's include it to show the full amount.
        $academic_base = $fs['admission_fee'] + $fs['base_fee'] + $fs['lab_charges'] + 
                         $fs['library_fee'] + $fs['exam_fee'] + $fs['registration_fee'] + 
                         $fs['sports_fund'] + $fs['library_security'] + $fs['it_services'];
        
        $academic_base += (18 * $fs['credit_hour_rate']); // 18 CH standard
        
        $academic_discount = ($s['scholarship_percent'] > 0) ? ($academic_base * $s['scholarship_percent'] / 100) : ($s['scholarship_fixed'] ?? 0);
        $academic_payable = max(0, $academic_base - $academic_discount);
        $due_date = date('Y-m-d', strtotime('+15 days'));

        $invStmt = $pdo->prepare("INSERT INTO invoices (user_id, semester_id, invoice_type, total_base_amount, discount_amount, payable_amount, balance_due, due_date, status) VALUES (?, ?, 'academic', ?, ?, ?, ?, ?, 'unpaid')");
        $invStmt->execute([$s['id'], $semester_id, $academic_base, $academic_discount, $academic_payable, $academic_payable, $due_date]);
        $count++;
    }
    echo "SUCCESS: $count fresh vouchers generated for today's demo.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
