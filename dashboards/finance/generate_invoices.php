<?php 
require_once '../../includes/header.php'; 

// Handle Bulk Generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_invoices'])) {
    $semester_id = $_POST['semester_id'];
    
    // Fetch students FIRST to know which categories we need
    $stuStmt = $pdo->prepare("SELECT id, name, fee_category, scholarship_percent, scholarship_fixed FROM users WHERE semester_id = ? AND role = 'student'");
    $stuStmt->execute([$semester_id]);
    $students = $stuStmt->fetchAll();

    if (empty($students)) {
        $error = "No students found in this semester!";
    } else {
        // Fetch all fee structures for this semester once
        $feeStmt = $pdo->prepare("SELECT * FROM fee_structures WHERE semester_id = ?");
        $feeStmt->execute([$semester_id]);
        $raw_fees = $feeStmt->fetchAll();
        $fees_by_cat = [];
        foreach($raw_fees as $rf) {
            $fees_by_cat[$rf['fee_category']] = $rf;
        }

        $count = 0;
        $missing_cats = [];

        foreach($students as $s) {
            $cat = $s['fee_category'] ?: 'Regular';
            if (!isset($fees_by_cat[$cat])) {
                $missing_cats[$cat] = true;
                continue;
            }

            $fs = $fees_by_cat[$cat];
            
            // Calculate Academic Fee (Everything except hostel)
            $academic_base = $fs['base_fee'] + $fs['lab_charges'] + $fs['library_fee'] + 
                             $fs['exam_fee'] + $fs['registration_fee'] + 
                             $fs['sports_fund'] + $fs['library_security'] + $fs['it_services'];
            
            // Calculate Hostel Fee
            $hostel_base = $fs['hostel_fee'] ?? 0;
            
            // Apply Scholarship ONLY to Academic portion
            $academic_discount = 0;
            if ($s['scholarship_percent'] > 0) {
                $academic_discount = ($academic_base * $s['scholarship_percent']) / 100;
            } elseif ($s['scholarship_fixed'] > 0) {
                $academic_discount = $s['scholarship_fixed'];
            }
            
            $academic_payable = max(0, $academic_base - $academic_discount);
            $due_date = date('Y-m-d', strtotime('+15 days'));
            
            // Generate Academic Invoice
            if ($academic_payable > 0) {
                $check = $pdo->prepare("SELECT id FROM invoices WHERE user_id = ? AND semester_id = ? AND invoice_type = 'academic'");
                $check->execute([$s['id'], $semester_id]);
                if ($check->rowCount() == 0) {
                    $invStmt = $pdo->prepare("INSERT INTO invoices (user_id, semester_id, invoice_type, total_base_amount, discount_amount, payable_amount, balance_due, due_date, status) VALUES (?, ?, 'academic', ?, ?, ?, ?, ?, 'unpaid')");
                    $invStmt->execute([$s['id'], $semester_id, $academic_base, $academic_discount, $academic_payable, $academic_payable, $due_date]);
                    $count++;
                }
            }

            // Generate Hostel Invoice (If applicable)
            if ($hostel_base > 0) {
                $checkH = $pdo->prepare("SELECT id FROM invoices WHERE user_id = ? AND semester_id = ? AND invoice_type = 'hostel'");
                $checkH->execute([$s['id'], $semester_id]);
                if ($checkH->rowCount() == 0) {
                    $invStmtH = $pdo->prepare("INSERT INTO invoices (user_id, semester_id, invoice_type, total_base_amount, discount_amount, payable_amount, balance_due, due_date, status) VALUES (?, ?, 'hostel', ?, 0, ?, ?, ?, 'unpaid')");
                    $invStmtH->execute([$s['id'], $semester_id, $hostel_base, $hostel_base, $hostel_base, $due_date]);
                    $count++;
                }
            }
        }

        if (!empty($missing_cats)) {
            $error = "Invoices partial: Missing fee structures for categories: " . implode(', ', array_keys($missing_cats));
        }
        $success = "Successfully generated $count new invoices.";
    }
}

// Fetch semesters for selection
$semesters = $pdo->query("
    SELECT s.*, p.name as program_name 
    FROM semesters s 
    JOIN programs p ON s.program_id = p.id 
    ORDER BY p.name, s.number
")->fetchAll();
?>

<div class="row">
    <div class="col-md-5">
        <div class="card card-warning card-outline shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h3 class="card-title fw-bold text-warning"><i class="bi bi-magic me-2"></i> Bulk Invoice Engine</h3>
            </div>
            <div class="card-body p-4">
                <?php if(isset($success)): ?> <div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle me-2"></i> <?= $success ?></div> <?php endif; ?>
                <?php if(isset($error)): ?> <div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div> <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Target Semester</label>
                        <select name="semester_id" class="form-select form-select-lg border-0 bg-light shadow-none" required style="border-radius: 10px;">
                            <option value="">-- Choose Semester --</option>
                            <?php foreach($semesters as $sem): ?>
                                <option value="<?= $sem['id'] ?>"><?= $sem['program_name'] ?> - <?= $sem['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-muted mt-2 mb-0 small"><i class="bi bi-info-circle me-1"></i> System will auto-apply category-based fees to each student.</p>
                    </div>
                    <button type="submit" name="generate_invoices" class="btn btn-warning btn-lg w-100 fw-bold py-3 shadow-sm" style="border-radius: 12px;">
                        Generate Batch Invoices
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h3 class="card-title fw-bold text-dark">Recent Activity</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0">Inv #</th>
                                <th class="border-0">Student</th>
                                <th class="border-0">Payable</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="pe-4 border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $pdo->query("
                                SELECT i.*, u.name, u.roll_no 
                                FROM invoices i 
                                JOIN users u ON i.user_id = u.id 
                                ORDER BY i.id DESC LIMIT 8
                            ");
                            while($r = $recent->fetch()): ?>
                            <tr>
                                <td class="ps-4">#<?= $r['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($r['name']) ?></div>
                                    <div class="text-muted small"><?= $r['roll_no'] ?> 
                                        <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size: 0.6rem;">
                                            <?= strtoupper($r['invoice_type']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark">PKR <?= number_format($r['payable_amount'], 0) ?></td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-<?= $r['status'] == 'paid' ? 'success' : 'danger-subtle text-danger' ?>">
                                        <?= strtoupper($r['status']) ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="fee_voucher.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                        <i class="bi bi-printer me-1"></i> Voucher
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
