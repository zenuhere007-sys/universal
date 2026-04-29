<?php 
require_once '../../includes/header.php'; 

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dispatch_vouchers'])) {
    $invoice_ids = $_POST['invoice_ids'] ?? [];
    if (!empty($invoice_ids)) {
        $placeholders = implode(',', array_fill(0, count($invoice_ids), '?'));
        $stmt = $pdo->prepare("UPDATE invoices SET status = 'unpaid' WHERE id IN ($placeholders) AND status = 'draft'");
        $stmt->execute($invoice_ids);
        $success = count($invoice_ids) . " vouchers dispatched to students successfully!";
    }
}

$drafts = $pdo->query("
    SELECT i.*, u.name as student_name, u.roll_no, p.name as prog_name, s.name as sem_name 
    FROM invoices i 
    JOIN users u ON i.user_id = u.id 
    JOIN semesters s ON i.semester_id = s.id 
    JOIN programs p ON s.program_id = p.id 
    WHERE i.status = 'draft' 
    ORDER BY i.created_at DESC
")->fetchAll();
?>

<style>
    .search-pill { border-radius: 30px; border: 1px solid var(--glass-border); padding: 10px 20px; transition: all 0.3s; background: var(--glass-bg); color: var(--glass-text); }
    .search-pill:focus { box-shadow: 0 0 15px rgba(0,123,255,0.1); border-color: #007bff; outline: none; }
    .status-badge { font-size: 0.65rem; font-weight: 800; padding: 5px 12px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.5px; }
    .avatar-circle { width: 35px; height: 35px; background: rgba(0,0,0,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--glass-text); margin-right: 12px; }
    [data-bs-theme="dark"] .avatar-circle { background: rgba(255,255,255,0.1); }
</style>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h3 class="fw-bold mb-0">Voucher Dispatch Center</h3>
        <p class="text-muted small">Process and release fee invoices to student portals</p>
    </div>
    <div class="col-auto">
        <input type="text" id="studentSearch" class="search-pill shadow-sm" placeholder="Search student or roll no...">
    </div>
</div>

<div class="card glass-card">
    <div class="card-body p-0">
        <?php if($success): ?> 
            <div class="alert alert-success m-4 border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> <?= $success ?></div> 
        <?php endif; ?>
        
        <form method="POST" id="dispatchForm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="vouchersTable">
                    <thead class="bg-light opacity-75">
                        <tr>
                            <th width="50" class="ps-4 text-center">
                                <div class="form-check m-0 d-inline-block">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </div>
                            </th>
                            <th>Invoice Detail</th>
                            <th>Program & Semester</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($drafts as $d): ?>
                        <tr>
                            <td class="ps-4 text-center">
                                <div class="form-check m-0 d-inline-block">
                                    <input type="checkbox" name="invoice_ids[]" value="<?= $d['id'] ?>" class="rowCheck form-check-input">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle"><?= substr($d['student_name'], 0, 1) ?></div>
                                    <div>
                                        <div class="fw-bold text-dark">#INV-<?= $d['id'] ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($d['student_name']) ?> (<?= $d['roll_no'] ?>)</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold"><?= $d['prog_name'] ?></div>
                                <div class="x-small text-muted"><?= $d['sem_name'] ?></div>
                            </td>
                            <td class="fw-bold">PKR <?= number_format($d['payable_amount'], 0) ?></td>
                            <td><span class="status-badge bg-secondary-subtle text-secondary border border-secondary"><?= $d['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($drafts)): ?>
                            <tr><td colspan="5" class="text-center py-5">
                                <i class="bi bi-mailbox2 fs-1 text-muted d-block mb-3"></i>
                                <span class="text-muted fw-bold">No pending vouchers to dispatch.</span>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if(!empty($drafts)): ?>
            <div class="p-4 bg-light bg-opacity-50 border-top text-end rounded-bottom-4">
                <span class="text-muted small me-3 selected-count">0 selected for dispatch</span>
                <button type="submit" name="dispatch_vouchers" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-send-check me-2"></i> Dispatch Vouchers
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checks = document.querySelectorAll('.rowCheck');
    checks.forEach(c => c.checked = this.checked);
    updateCount();
});

document.querySelectorAll('.rowCheck').forEach(c => {
    c.addEventListener('change', updateCount);
});

function updateCount() {
    const checked = document.querySelectorAll('.rowCheck:checked').length;
    const counter = document.querySelector('.selected-count');
    if(counter) counter.innerText = checked + ' selected for dispatch';
}

// Simple Search Logic
document.getElementById('studentSearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelectorAll('#vouchersTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toUpperCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    });
});
</script>

<style>
.x-small { font-size: 0.75rem; }
</style>

<?php require_once '../../includes/footer.php'; ?>
