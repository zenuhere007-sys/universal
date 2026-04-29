<?php 
require_once '../../includes/header.php'; 

$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $invoice_id = $_POST['invoice_id'];
    $action = $_POST['action']; 
    
    if ($action === 'verify') {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE payments SET verification_status = 'verified' WHERE id = ?");
            $stmt->execute([$payment_id]);
            
            $pStmt = $pdo->prepare("SELECT amount FROM payments WHERE id = ?");
            $pStmt->execute([$payment_id]);
            $amount_paid = $pStmt->fetchColumn();

            $inv = $pdo->prepare("SELECT balance_due FROM invoices WHERE id = ?");
            $inv->execute([$invoice_id]);
            $current_balance = $inv->fetchColumn();
            
            $new_balance = $current_balance - $amount_paid;
            $status = ($new_balance <= 0) ? 'paid' : 'partial';
            
            $upd = $pdo->prepare("UPDATE invoices SET balance_due = ?, status = ? WHERE id = ?");
            $upd->execute([$new_balance, $status, $invoice_id]);
            
            $pdo->commit();
            $success = "Payment verified and student balance updated!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $stmt = $pdo->prepare("UPDATE payments SET verification_status = 'rejected' WHERE id = ?");
        $stmt->execute([$payment_id]);
        $error = "Payment proof rejected.";
    }
}

$pendings = $pdo->query("
    SELECT p.*, i.payable_amount, i.balance_due, u.name as student_name, u.roll_no 
    FROM payments p 
    JOIN invoices i ON p.invoice_id = i.id 
    JOIN users u ON i.user_id = u.id 
    WHERE p.verification_status = 'pending' 
    ORDER BY p.paid_date DESC
")->fetchAll();
?>

<style>
    .status-pill { font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 12px; text-transform: uppercase; }
    .avatar-circle { width: 35px; height: 35px; background: rgba(0,0,0,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--glass-text); margin-right: 12px; }
    [data-bs-theme="dark"] .avatar-circle { background: rgba(255,255,255,0.1); }
    .proof-thumb { width: 100px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; transition: 0.3s; }
    .proof-thumb:hover { filter: brightness(0.8); }
</style>

<div class="row mb-4">
    <div class="col">
        <h3 class="fw-bold mb-0">Payment Verification Queue</h3>
        <p class="text-muted small">Confirm bank receipts and manual payments from students</p>
    </div>
</div>

<div class="card glass-card">
    <div class="card-body p-0">
        <?php if($success): ?> <div class="alert alert-success m-4 border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> <?= $success ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger m-4 border-0 shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?></div> <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light opacity-75">
                    <tr>
                        <th class="ps-4">Student</th>
                        <th>Amount Paid</th>
                        <th>Method & Date</th>
                        <th>Proof Slip</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pendings as $p): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle text-primary bg-primary-subtle"><?= substr($p['student_name'], 0, 1) ?></div>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['student_name']) ?></div>
                                    <small class="text-muted"><?= $p['roll_no'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold text-success">PKR <?= number_format($p['amount'], 0) ?></td>
                        <td>
                            <div class="small fw-bold"><?= strtoupper($p['payment_method']) ?></div>
                            <div class="x-small text-muted"><?= date('d M Y', strtotime($p['paid_date'])) ?></div>
                        </td>
                        <td>
                            <?php if($p['proof_image']): ?>
                                <img src="../../<?= $p['proof_image'] ?>" class="proof-thumb border" 
                                     onclick="showProofModal('../../<?= $p['proof_image'] ?>')">
                            <?php else: ?>
                                <span class="text-muted italic small">No image provided</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 text-end">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="invoice_id" value="<?= $p['invoice_id'] ?>">
                                <button type="submit" name="action" value="verify" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Approve
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($pendings)): ?>
                        <tr><td colspan="5" class="text-center py-5">
                            <i class="bi bi-check2-all fs-1 text-success d-block mb-3"></i>
                            <span class="text-muted fw-bold">All payments verified. Queue is empty!</span>
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Proof Preview Modal -->
<div class="modal fade" id="proofModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold"><i class="bi bi-image me-2 text-primary"></i>Payment Proof Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="proofFullImg" src="" class="img-fluid rounded-3 shadow-sm border" style="max-height: 70vh;">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close Preview</button>
            </div>
        </div>
    </div>
</div>

<script>
function showProofModal(imgSrc) {
    document.getElementById('proofFullImg').src = imgSrc;
    new bootstrap.Modal(document.getElementById('proofModal')).show();
}
</script>

<style>
.x-small { font-size: 0.7rem; }
.italic { font-style: italic; }
</style>

<?php require_once '../../includes/footer.php'; ?>
