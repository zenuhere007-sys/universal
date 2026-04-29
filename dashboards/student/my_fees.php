<?php 
require_once '../../includes/header.php'; 

$user_id = $_SESSION['user_id'];

// Handle Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $invoice_id = $_POST['invoice_id'];
    $type = $_POST['request_type'];
    $desc = trim($_POST['description']);
    
    $reqStmt = $pdo->prepare("INSERT INTO student_requests (user_id, invoice_id, request_type, description) VALUES (?, ?, ?, ?)");
    $reqStmt->execute([$user_id, $invoice_id, $type, $desc]);
    $_SESSION['success_msg'] = "Your request has been submitted successfully and is pending review.";
    echo "<script>window.location.href='my_fees.php';</script>";
    exit;
}

// Fetch student's program and duration
$progStmt = $pdo->prepare("SELECT p.name, p.total_semesters FROM users u JOIN programs p ON u.program_id = p.id WHERE u.id = ?");
$progStmt->execute([$user_id]);
$program = $progStmt->fetch();
$total_semesters = $program ? $program['total_semesters'] : 8;

// Fetch student's invoices and group them by semester logic
$stmt = $pdo->prepare("
    SELECT i.*, s.name as sem_name, s.number as sem_num, p.name as prog_name 
    FROM invoices i 
    JOIN semesters s ON i.semester_id = s.id 
    JOIN programs p ON s.program_id = p.id 
    WHERE i.user_id = ? 
    ORDER BY s.number ASC
");
$stmt->execute([$user_id]);
$raw_invoices = $stmt->fetchAll();
$invoices_by_sem = [];
foreach($raw_invoices as $inv) {
    $invoices_by_sem[$inv['sem_num']] = $inv;
}

// Fetch active installments
$instStmt = $pdo->prepare("
    SELECT inst.*, i.semester_id 
    FROM installments inst 
    JOIN invoices i ON inst.invoice_id = i.id 
    WHERE i.user_id = ? AND inst.status = 'pending'
    ORDER BY inst.due_date ASC
");
$instStmt->execute([$user_id]);
$installments = $instStmt->fetchAll();

// Fetch student's requests
$allReqs = $pdo->prepare("
    SELECT r.*, i.id as inv_no
    FROM student_requests r
    LEFT JOIN invoices i ON r.invoice_id = i.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$allReqs->execute([$user_id]);
$my_requests = $allReqs->fetchAll();
?>

<style>
    .request-btn { border-radius: 20px; font-weight: 600; padding: 5px 15px; }
    .status-pill { font-size: 0.7rem; padding: 4px 10px; border-radius: 12px; font-weight: 700; text-transform: uppercase; }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card glass-card shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 text-primary me-2"></i>My Fee Summary</h5>
                <p class="text-muted small mt-1">Review and manage your semester invoices</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4">Invoice Details</th>
                                <th>Amount</th>
                                <th>Balance Due</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i = 1; $i <= $total_semesters; $i++): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 0.8rem; font-weight: bold;">
                                            <?= $i ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">Semester <?= $i ?></div>
                                            <?php if(isset($invoices_by_sem[$i])): ?>
                                                <small class="text-muted">#INV-<?= $invoices_by_sem[$i]['id'] ?></small>
                                            <?php else: ?>
                                                <small class="text-muted italic">Voucher not generated</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(isset($invoices_by_sem[$i])): ?>
                                        PKR <?= number_format($invoices_by_sem[$i]['payable_amount'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($invoices_by_sem[$i])): ?>
                                        <span class="text-danger fw-bold">PKR <?= number_format($invoices_by_sem[$i]['balance_due'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($invoices_by_sem[$i])): 
                                        $badge = 'bg-danger';
                                        if($invoices_by_sem[$i]['status'] === 'paid') $badge = 'bg-success';
                                        if($invoices_by_sem[$i]['status'] === 'partial') $badge = 'bg-warning text-dark';
                                    ?>
                                        <span class="status-pill <?= $badge ?>"><?= $invoices_by_sem[$i]['status'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.6rem;">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if(isset($invoices_by_sem[$i])): ?>
                                            <a href="receipt.php?invoice_id=<?= $invoices_by_sem[$i]['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill" target="_blank" title="Receipt">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <?php if($invoices_by_sem[$i]['balance_due'] > 0): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                                        onclick="openRequestModal(<?= $invoices_by_sem[$i]['id'] ?>)">
                                                    <i class="bi bi-chat-dots me-1"></i> Request
                                                </button>
                                                <a href="pay_fee.php?invoice_id=<?= $invoices_by_sem[$i]['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                                    Pay Now
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small italic">Coming Soon</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endfor; ?>
                            <?php if(empty($invoices)): ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">No invoices found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Request Status Card -->
        <div class="card glass-card shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-info me-2"></i>Request History</h5>
                <p class="text-muted small mt-1">Track the status of your applications</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4">Request Type</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="pe-4">Response</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($my_requests as $req): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark text-capitalize"><?= str_replace('_', ' ', $req['request_type']) ?></div>
                                    <small class="text-muted"><?= date('d M, Y', strtotime($req['created_at'])) ?></small>
                                </td>
                                <td><div class="small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($req['description']) ?></div></td>
                                <td>
                                    <?php 
                                    $s_badge = 'bg-secondary';
                                    if($req['status'] === 'approved') $s_badge = 'bg-success';
                                    if($req['status'] === 'rejected') $s_badge = 'bg-danger';
                                    ?>
                                    <span class="status-pill <?= $s_badge ?>"><?= $req['status'] ?></span>
                                </td>
                                <td class="pe-4 italic small"><?= $req['admin_remarks'] ?: '<span class="text-muted">Waiting for review...</span>' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($my_requests)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No requests submitted yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <?php if(!empty($installments)): ?>
        <div class="card glass-card shadow-sm border-start border-warning border-4 mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0 text-warning text-uppercase small">Payment Calendar</h6>
            </div>
            <div class="card-body px-4">
                <?php foreach($installments as $inst): ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-bold">Installment #<?= $inst['installment_no'] ?></div>
                        <small class="text-muted">Due: <?= date('d M, Y', strtotime($inst['due_date'])) ?></small>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-dark">PKR <?= number_format($inst['amount'], 2) ?></div>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle small px-2">PENDING</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card glass-card shadow-sm bg-primary text-white">
            <div class="card-body p-4 text-center">
                <i class="bi bi-info-circle-fill fs-2 mb-3"></i>
                <h6 class="fw-bold">Need Help?</h6>
                <p class="small opacity-75">You can apply for installments, scholarships, or fine waivers directly from your invoice list using the "Request" button.</p>
            </div>
        </div>
    </div>
</div>

<!-- Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white border-0" style="border-radius: 20px 20px 0 0; padding: 20px;">
                <h5 class="modal-title fw-bold"><i class="bi bi-chat-dots me-2"></i>Invoice Support Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-toggle="modal" data-bs-target="#requestModal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="invoice_id" id="modal_invoice_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">What do you need help with?</label>
                    <select name="request_type" class="form-select border-0 bg-light py-2" required style="border-radius: 10px;">
                        <option value="installment">Request Installment Plan</option>
                        <option value="scholarship">Apply for Scholarship / Fee Relief</option>
                        <option value="date_extension">Request Due Date Extension</option>
                        <option value="fine_waiver">Request Late Fee (Fine) Waiver</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold text-muted text-uppercase">Reason / Description</label>
                    <textarea name="description" class="form-control border-0 bg-light" rows="4" placeholder="Briefly explain why you are making this request..." required style="border-radius: 10px;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="submit" name="submit_request" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Send Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRequestModal(invoiceId) {
    document.getElementById('modal_invoice_id').value = invoiceId;
    var myModal = new bootstrap.Modal(document.getElementById('requestModal'));
    myModal.show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>
