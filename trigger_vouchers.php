<?php
require_once 'core/db.php';

try {
    $semester_id = 5; // BBIS Semester 8
    
    // Fetch students
    $stuStmt = $pdo->prepare("SELECT id, name, fee_category, scholarship_percent, scholarship_fixed FROM users WHERE semester_id = ? AND role = 'student'");
    $stuStmt->execute([$semester_id]);
    $students = $stuStmt->fetchAll();

    if (empty($students)) {
        echo "ERROR: No students found in BBIS Semester 8!";
        exit;
    }

    // Fetch fee structures
    $feeStmt = $pdo->prepare("SELECT * FROM fee_structures WHERE semester_id = ?");
    $feeStmt->execute([$semester_id]);
    $raw_fees = $feeStmt->fetchAll();
    $fees_by_cat = [];
    foreach($raw_fees as $rf) {
        $fees_by_cat[$rf['fee_category']] = $rf;
    }

    $count = 0;
    foreach($students as $s) {
        $cat = $s['fee_category'] ?: 'Regular';
        if (!isset($fees_by_cat[$cat])) continue;

        $fs = $fees_by_cat[$cat];
        $academic_base = $fs['base_fee'] + $fs['lab_charges'] + $fs['library_fee'] + 
                         $fs['exam_fee'] + $fs['registration_fee'] + 
                         $fs['sports_fund'] + $fs['library_security'] + $fs['it_services'];
        
        // Add credit hour based tuition if available (18 CH * rate)
        $academic_base += (18 * $fs['credit_hour_rate']);
        
        $academic_discount = ($s['scholarship_percent'] > 0) ? ($academic_base * $s['scholarship_percent'] / 100) : ($s['scholarship_fixed'] ?? 0);
        $academic_payable = max(0, $academic_base - $academic_discount);
        $due_date = date('Y-m-d', strtotime('+15 days'));

        $check = $pdo->prepare("SELECT id FROM invoices WHERE user_id = ? AND semester_id = ? AND invoice_type = 'academic'");
        $check->execute([$s['id'], $semester_id]);
        
        if ($check->rowCount() == 0) {
            $invStmt = $pdo->prepare("INSERT INTO invoices (user_id, semester_id, invoice_type, total_base_amount, discount_amount, payable_amount, balance_due, due_date, status) VALUES (?, ?, 'academic', ?, ?, ?, ?, ?, 'unpaid')");
            $invStmt->execute([$s['id'], $semester_id, $academic_base, $academic_discount, $academic_payable, $academic_payable, $due_date]);
            $count++;
        }
    }
    echo "SUCCESS: Generated $count professional vouchers for BBIS students.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
