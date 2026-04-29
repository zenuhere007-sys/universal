<?php 
require_once '../../includes/header.php'; 

// 2. Handle Cancellation of Installment Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_plan'])) {
    $invoice_id = $_POST['invoice_id'];
    $stmt = $pdo->prepare("DELETE FROM installments WHERE invoice_id = ?");
    $stmt->execute([$invoice_id]);
    $_SESSION['success_msg'] = "Installment plan cancelled successfully.";
    echo "<script>window.location.href='installments.php';</script>";
    exit;
}

// 1. Handle Creation of Installment Plan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_installments'])) {
    $invoice_id = $_POST['invoice_id'];
    $num_installments = $_POST['num_installments'];
    $interval_days = $_POST['interval_days'];

    $invStmt = $pdo->prepare("SELECT payable_amount, user_id FROM invoices WHERE id = ?");
    $invStmt->execute([$invoice_id]);
    $invoice = $invStmt->fetch();

    if ($invoice) {
        $amount_per_inst = round($invoice['payable_amount'] / $num_installments);
        
        try {
            $pdo->beginTransaction();
            
            // Clear existing plan if any
            $pdo->prepare("DELETE FROM installments WHERE invoice_id = ?")->execute([$invoice_id]);

            for ($i = 1; $i <= $num_installments; $i++) {
                // Adjust for rounding in last installment
                if ($i == $num_installments) {
                    $amount_per_inst = $invoice['payable_amount'] - ($amount_per_inst * ($num_installments - 1));
                }
                
                $due_date = date('Y-m-d', strtotime("+" . (($i - 1) * $interval_days) . " days"));
                $stmt = $pdo->prepare("INSERT INTO installments (invoice_id, installment_no, amount, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$invoice_id, $i, $amount_per_inst, $due_date]);
            }

            $pdo->commit();
            $_SESSION['success_msg'] = "Installment plan of $num_installments parts created successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_msg'] = "Failed to create plan: " . $e->getMessage();
        }
    }
    echo "<script>window.location.href='installments.php';</script>";
    exit;
}

// Fetch Invoices that don't have installments yet (or allowed for reset)
$invoices = $pdo->query("
    SELECT i.id, i.payable_amount, i.total_base_amount, i.discount_amount, u.name, u.roll_no, i.invoice_type
    FROM invoices i 
    JOIN users u ON i.user_id = u.id 
    WHERE i.status != 'paid'
    ORDER BY i.id DESC
")->fetchAll();

// Group installments by invoice for the timeline view
$active_plans = $pdo->query("
    SELECT inst.*, u.name as student_name, u.roll_no, 
           i.payable_amount as total_bill, i.invoice_type, i.discount_amount
    FROM installments inst
    JOIN invoices i ON inst.invoice_id = i.id
    JOIN users u ON i.user_id = u.id
    ORDER BY inst.invoice_id, inst.installment_no
")->fetchAll(PDO::FETCH_GROUP);
?>

<style>
    .timeline-card { border: none; border-radius: 16px; margin-bottom: 20px; }
    .timeline-steps { position: relative; display: flex; justify-content: space-between; align-items: center; padding: 20px 0; }
    .timeline-steps::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 2px; background: #e9ecef; z-index: 1; transform: translateY(-50%); }
    .step-item { position: relative; z-index: 2; background: #fff; width: 40px; height: 40px; border-radius: 50%; border: 3px solid #e9ecef; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem; }
    .step-item.active { border-color: #0d6efd; color: #0d6efd; box-shadow: 0 0 10px rgba(13,110,253,0.2); }
    .step-item.paid { background: #198754; border-color: #198754; color: #fff; }
    .step-label { position: absolute; top: 45px; left: 50%; transform: translateX(-50%); white-space: nowrap; font-size: 0.7rem; color: #6c757d; font-weight: 600; }
    .gradient-btn { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none; }
    .form-box { background: #f8f9fa; border-radius: 15px; padding: 25px; border: 1px solid #eee; }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?></div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card timeline-card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i> Plan Designer</h5>
                <p class="text-muted small">Split any invoice into manageable parts</p>
            </div>
            <div class="card-body px-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">1. Filter by Program</label>
                        <?php $progs = $pdo->query("SELECT * FROM programs ORDER BY name")->fetchAll(); ?>
                        <select id="filterProgram" class="form-select bg-light border-0 py-2 mb-2" style="border-radius: 10px;">
                            <option value="">-- All Programs --</option>
                            <?php foreach($progs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label class="form-label small fw-bold">2. Search Student/Roll No</label>
                        <input type="text" id="stuQuery" class="form-control bg-light border-0 py-2 mb-3" placeholder="Type name or roll no..." style="border-radius: 10px;">

                        <label class="form-label small fw-bold">3. Select Active Bill</label>
                        <select name="invoice_id" id="invoiceSelect" class="form-select bg-light border-0 py-2" required style="border-radius: 10px;">
                            <option value="">-- No invoices found --</option>
                        </select>
                    </div>

                    <script>
                        const progFilter = document.getElementById('filterProgram');
                        const queryInput = document.getElementById('stuQuery');
                        const invoiceSelect = document.getElementById('invoiceSelect');

                        function fetchInvoices() {
                            const pid = progFilter.value;
                            const query = queryInput.value;
                            
                            fetch(`../../core/search_students.php?type=invoice&program_id=${pid}&query=${query}`)
                                .then(res => res.json())
                                .then(data => {
                                    invoiceSelect.innerHTML = '<option value="">-- Choose Invoice --</option>';
                                    if(data.length === 0) {
                                        invoiceSelect.innerHTML = '<option value="">-- No matches found --</option>';
                                    }
                                    data.forEach(inv => {
                                        const opt = document.createElement('option');
                                        opt.value = inv.id;
                                        opt.textContent = `${inv.roll_no} - ${inv.name} (PKR ${parseInt(inv.payable_amount).toLocaleString()})`;
                                        invoiceSelect.appendChild(opt);
                                    });
                                });
                        }

                        progFilter.onchange = fetchInvoices;
                        queryInput.onkeyup = fetchInvoices;
                        
                        // Initial Load
                        fetchInvoices();
                    </script>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Divisions</label>
                            <input type="number" name="num_installments" class="form-control bg-light border-0 py-2" value="2" min="2" max="6" required style="border-radius: 10px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Interval (Days)</label>
                            <input type="number" name="interval_days" class="form-control bg-light border-0 py-2" value="30" min="1" required style="border-radius: 10px;">
                        </div>
                    </div>
                    <button type="submit" name="create_installments" class="btn btn-primary w-100 fw-bold py-2 gradient-btn shadow-sm" style="border-radius: 10px;">
                        Set Installment Plan
                    </button>
                    <div class="mt-3 p-3 bg-info bg-opacity-10 rounded-3 small text-info">
                        <i class="bi bi-info-circle-fill me-2"></i> System will calculate equal parts and shift due dates automatically.
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card timeline-card shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold"><i class="bi bi-kanban text-success me-2"></i> Active Payment Timelines</h5>
                <p class="text-muted small">Monitoring ongoing installment agreements</p>
            </div>
            <div class="card-body p-4">
                <?php if(empty($active_plans)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar2-x fs-1 d-block mb-2"></i>
                        No active installment plans found.
                    </div>
                <?php endif; ?>

                <?php foreach($active_plans as $inv_id => $plan): 
                    $first = $plan[0];
                ?>
                <div class="plan-group mb-5 pb-4 border-bottom last-child-border-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0">
                                <?= htmlspecialchars($first['student_name']) ?> 
                                <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size: 0.6rem; vertical-align: middle;">
                                    <?= strtoupper($first['invoice_type']) ?>
                                </span>
                            </h6>
                            <small class="text-muted">Invoice #<?= $inv_id ?> • Total: <b>PKR <?= number_format($first['total_bill'], 0) ?></b> 
                                <?php if($first['discount_amount'] > 0): ?>
                                    <span class="text-success ms-2"><i class="bi bi-gift-fill me-1"></i> Scholarship Applied</span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this installment plan?');">
                            <input type="hidden" name="invoice_id" value="<?= $inv_id ?>">
                            <button type="submit" name="cancel_plan" class="btn btn-sm btn-outline-danger border-0">
                                <i class="bi bi-x-circle me-1"></i> Cancel Plan
                            </button>
                        </form>
                    </div>

                    <div class="timeline-steps px-5">
                        <?php foreach($plan as $inst): 
                            $is_paid = $inst['status'] == 'paid';
                            $is_overdue = (strtotime($inst['due_date']) < time() && !$is_paid);
                        ?>
                        <div class="step-item <?= $is_paid ? 'paid' : 'active' ?> <?= $is_overdue ? 'border-danger text-danger' : '' ?>">
                            <?= $is_paid ? '<i class="bi bi-check-lg"></i>' : $inst['installment_no'] ?>
                            <div class="step-label">
                                <span class="d-block text-dark mb-1">PKR <?= number_format($inst['amount'], 0) ?></span>
                                <span class="<?= $is_overdue ? 'text-danger fw-bold' : '' ?>"><?= date('d M', strtotime($inst['due_date'])) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
