<?php
require_once '../../core/db.php';
require_once '../../core/session.php';

$invoice_id = $_GET['id'] ?? 0;
$installment_id = $_GET['inst_id'] ?? 0;

$inst_data = null;
if ($installment_id) {
    $stmt = $pdo->prepare("SELECT * FROM installments WHERE id = ?");
    $stmt->execute([$installment_id]);
    $inst_data = $stmt->fetch();
    if ($inst_data) $invoice_id = $inst_data['invoice_id'];
}

// Fetch Invoice, Student, Semester, and Program details
$stmt = $pdo->prepare("
    SELECT i.*, u.name as student_name, u.roll_no, u.fee_category, 
           s.name as semester_name, p.name as program_name,
           fs.base_fee, fs.exam_fee, fs.registration_fee, fs.lab_charges, 
           fs.library_fee, fs.it_services, fs.sports_fund, fs.hostel_fee, fs.library_security
    FROM invoices i
    JOIN users u ON i.user_id = u.id
    JOIN semesters s ON i.semester_id = s.id
    JOIN programs p ON s.program_id = p.id
    LEFT JOIN fee_structures fs ON (s.id = fs.semester_id AND u.fee_category = fs.fee_category)
    WHERE i.id = ?
");
$stmt->execute([$invoice_id]);
$inv = $stmt->fetch();

if (!$inv) {
    die("Invoice not found.");
}

// System Settings for branding
$settings = [];
$st = $pdo->query("SELECT * FROM system_settings");
while($r = $st->fetch()) $settings[$r['setting_key']] = $r['setting_value'];

function renderVoucher($inv, $settings, $copy_name, $inst_data = null) {
    $type = $inv['invoice_type'] ?? 'academic';
    $label = ($type == 'hostel') ? 'Hostel Fee Voucher' : 'Academic Fee Voucher';
    
    $total_base = $inv['total_base_amount'];
    $discount = $inv['discount_amount'];
    $payable = $inv['payable_amount'];
    $due_date = $inv['due_date'] ?? 'N/A';
    
    if ($inst_data) {
        $payable = $inst_data['amount'];
        $due_date = $inst_data['due_date'];
        $copy_name .= " (Inst #".$inst_data['installment_no'].")";
    }
    ?>
    <div class="voucher-copy">
        <div class="header d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="logo-box me-2">UNIV</div>
                <div>
                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($settings['system_name']) ?></h5>
                    <small class="text-muted"><?= $label ?></small>
                </div>
            </div>
            <div class="text-end">
                <span class="badge border border-dark text-dark px-3 mt-1" style="font-size: 0.65rem;"><?= strtoupper($copy_name) ?></span>
                <div class="mt-1 small fw-bold text-uppercase">Challan #<?= $inv['id'] ?><?= $inst_data ? '-I'.$inst_data['installment_no'] : '' ?></div>
            </div>
        </div>

        <div class="info-grid mb-3">
            <div class="row g-0">
                <div class="col-6 border p-2">
                    <small class="text-muted d-block">Student Name</small>
                    <div class="fw-bold small"><?= htmlspecialchars($inv['student_name']) ?></div>
                </div>
                <div class="col-6 border p-2">
                    <small class="text-muted d-block">Roll No / ID</small>
                    <div class="fw-bold small"><?= $inv['roll_no'] ?></div>
                </div>
                <div class="col-7 border p-2">
                    <small class="text-muted d-block">Program & Semester</small>
                    <div class="fw-bold small" style="font-size: 0.75rem;"><?= $inv['program_name'] ?> - <?= $inv['semester_name'] ?></div>
                </div>
                <div class="col-5 border p-2">
                    <small class="text-muted d-block">Due Date</small>
                    <div class="fw-bold text-danger"><?= date('d-M-Y', strtotime($due_date)) ?></div>
                </div>
            </div>
        </div>

        <?php if(!$inst_data): ?>
        <table class="table table-sm table-bordered mb-3 fee-table">
            <thead class="bg-light">
                <tr><th>Description</th><th class="text-end">Amount</th></tr>
            </thead>
            <tbody>
                <?php if($type == 'academic'): ?>
                    <tr><td>Tuition Fee (Base)</td><td class="text-end"><?= number_format($inv['base_fee'], 0) ?></td></tr>
                    <?php if($inv['exam_fee'] > 0): ?><tr><td>Examination Fee</td><td class="text-end"><?= number_format($inv['exam_fee'], 0) ?></td></tr><?php endif; ?>
                    <?php if($inv['registration_fee'] > 0): ?><tr><td>Registration Fee</td><td class="text-end"><?= number_format($inv['registration_fee'], 0) ?></td></tr><?php endif; ?>
                    <?php if($inv['lab_charges'] > 0): ?><tr><td>Lab Charges</td><td class="text-end"><?= number_format($inv['lab_charges'], 0) ?></td></tr><?php endif; ?>
                    <?php if($inv['library_fee'] > 0): ?><tr><td>Library Fee</td><td class="text-end"><?= number_format($inv['library_fee'], 0) ?></td></tr><?php endif; ?>
                    <?php if($inv['it_services'] > 0): ?><tr><td>IT Services</td><td class="text-end"><?= number_format($inv['it_services'], 0) ?></td></tr><?php endif; ?>
                    <?php if($inv['sports_fund'] > 0): ?><tr><td>Sports Fund</td><td class="text-end"><?= number_format($inv['sports_fund'], 0) ?></td></tr><?php endif; ?>
                    <?php if($inv['library_security'] > 0): ?><tr><td>Lib. Security</td><td class="text-end"><?= number_format($inv['library_security'], 0) ?></td></tr><?php endif; ?>
                <?php else: // Hostel ?>
                    <tr><td>Hostel Accommodation Fee</td><td class="text-end"><?= number_format($inv['hostel_fee'], 0) ?></td></tr>
                <?php endif; ?>
                
                <tr class="fw-bold"><td>TOTAL</td><td class="text-end"><?= number_format($total_base, 0) ?></td></tr>
                <?php if($discount > 0): ?><tr><td class="text-muted">Scholarship</td><td class="text-end">-<?= number_format($discount, 0) ?></td></tr><?php endif; ?>
                <?php if($inv['fine_amount'] > 0): ?><tr><td class="text-danger">Late Fines</td><td class="text-end">+<?= number_format($inv['fine_amount'], 0) ?></td></tr><?php endif; ?>
                <tr class="fw-bold bg-light"><td>NET PAYABLE</td><td class="text-end text-primary">PKR <?= number_format($payable, 0) ?></td></tr>
            </tbody>
        </table>
        <?php else: ?>
        <div class="border p-4 text-center mb-3 bg-light rounded shadow-sm">
            <div class="text-muted small mb-1"><?= strtoupper($type) ?> INSTALLMENT</div>
            <h3 class="fw-bold text-primary mb-0">PKR <?= number_format($payable, 0) ?></h3>
            <div class="text-muted x-small mt-2">Part <?= $inst_data['installment_no'] ?> of the total plan.</div>
        </div>
        <?php endif; ?>

        <div class="footer-note" style="font-size: 0.75rem;">
            <p class="mb-1 text-center border-top pt-2"><b>Instructions:</b> Please pay at any designated bank branch. Verification is required for final enrollment.</p>
            <div class="d-flex justify-content-between mt-4">
                <div class="text-center"><div class="border-top pt-1">Cashier</div></div>
                <div class="text-center"><div class="border-top pt-1">Officer</div></div>
            </div>
        </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Voucher #<?= $invoice_id ?></title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .voucher-container { background: white; width: 1100px; margin: 20px auto; padding: 10px; box-shadow: 0 0 30px rgba(0,0,0,0.1); border-radius: 8px; }
        .voucher-copy { border-right: 2px dashed #ccc; padding: 10px 15px; width: 33.33%; float: left; position: relative; min-height: 550px; }
        .voucher-copy:last-child { border-right: none; }
        .logo-box { background: #0d6efd; color: white; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; font-weight: bold; border-radius: 8px; }
        .fee-table { font-size: 0.75rem; }
        .info-grid { font-size: 0.8rem; }
        .x-small { font-size: 0.7rem; }
        @media print {
            body { background: white; }
            .voucher-container { box-shadow: none; margin: 0; width: 100%; padding: 0; }
            .btn-print { display: none; }
        }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="text-center mt-4 btn-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow rounded-pill px-5">
             Print Fee Voucher
        </button>
        <a href="generate_invoices.php" class="btn btn-link text-decoration-none text-muted ms-3">Go Back</a>
    </div>

    <div class="voucher-container clearfix">
        <?php renderVoucher($inv, $settings, "Bank", $inst_data); ?>
        <?php renderVoucher($inv, $settings, "University", $inst_data); ?>
        <?php renderVoucher($inv, $settings, "Student", $inst_data); ?>
    </div>
</body>
</html>
