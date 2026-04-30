<?php 
require_once '../../includes/header.php'; 

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_request'])) {
    $req_id = $_POST['request_id'];
    $status = $_POST['status'];
    $remarks = trim($_POST['admin_remarks']);
    
    $pdo->beginTransaction();
    try {
        // 1. Update Request Record
        $upd = $pdo->prepare("UPDATE student_requests SET status = ?, admin_remarks = ? WHERE id = ?");
        $upd->execute([$status, $remarks, $req_id]);
        
        // 2. Automated Logic if Approved
        if ($status === 'approved') {
            $reqQuery = $pdo->prepare("SELECT * FROM student_requests WHERE id = ?");
            $reqQuery->execute([$req_id]);
            $req = $reqQuery->fetch();
            
            if ($req['request_type'] === 'fine_waiver') {
                // Remove fine from invoice accurately
                $updInv = $pdo->prepare("UPDATE invoices SET payable_amount = payable_amount - fine_amount, balance_due = balance_due - fine_amount, fine_amount = 0 WHERE id = ?");
                $updInv->execute([$req['invoice_id']]);
            } elseif ($req['request_type'] === 'date_extension') {
                // Extend due date by 7 days
                $updInv = $pdo->prepare("UPDATE invoices SET due_date = DATE_ADD(due_date, INTERVAL 7 DAY) WHERE id = ?");
                $updInv->execute([$req['invoice_id']]);
            } elseif ($req['request_type'] === 'scholarship' || $req['request_type'] === 'installment') {
                // For scholarships and installments, approval is granted here, 
                // but actual calculation/assignment must be done in respective modules.
                // Attach a special remark to enforce this workflow.
                $admin_note = $remarks . "\n[System Note: Please proceed to the " . ucfirst($req['request_type']) . " module to finalize calculations and apply to student account.]";
                $updNote = $pdo->prepare("UPDATE student_requests SET admin_remarks = ? WHERE id = ?");
                $updNote->execute([$admin_note, $req_id]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success_msg'] = "Request #$req_id has been " . strtoupper($status) . ".";
        
        // Redirect to specific modules if needed
        if ($status === 'approved') {
             if ($req['request_type'] === 'scholarship') {
                 $_SESSION['success_msg'] .= " Please assign the scholarship now.";
                 echo "<script>window.location.href='manage_scholarships.php';</script>";
                 exit;
             } elseif ($req['request_type'] === 'installment') {
                 $_SESSION['success_msg'] .= " Please setup the installment plan now.";
                 echo "<script>window.location.href='installments.php';</script>";
                 exit;
             }
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Error updating request: " . $e->getMessage();
    }
    echo "<script>window.location.href='manage_requests.php';</script>";
    exit;
}

// Fetch all requests
$requests = $pdo->query("
    SELECT r.*, u.name as student_name, u.roll_no, i.id as inv_no, i.payable_amount, i.due_date
    FROM student_requests r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN invoices i ON r.invoice_id = i.id
    ORDER BY FIELD(r.status, 'pending', 'approved', 'rejected'), r.created_at DESC
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card glass-card shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title fw-bold"><i class="bi bi-patch-question me-2 text-primary"></i>Student Help Requests</h3>
                    <p class="text-muted small mb-0">Review and process student applications for fee relief and extensions</p>
                </div>
                <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                    <?= count($requests) ?> Total Submissions
                </div>
            </div>
            <div class="card-body p-0">
                <?php if(isset($_SESSION['success_msg'])): ?>
                    <div class="alert alert-success mx-4 my-2 border-0 shadow-sm small fw-bold">
                        <i class="bi bi-check-all me-2"></i> <?= $_SESSION['success_msg'] ?>
                    </div>
                    <?php unset($_SESSION['success_msg']); ?>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4">Student</th>
                                <th>Request Info</th>
                                <th>Invoice Details</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($requests as $r): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-primary"><?= htmlspecialchars($r['student_name']) ?></div>
                                    <div class="small text-muted"><?= $r['roll_no'] ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase small px-2">
                                        <?= str_replace('_', ' ', $r['request_type']) ?>
                                    </span>
                                    <div class="small text-muted mt-1"><?= date('d M, Y', strtotime($r['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="small"><b>#INV-<?= $r['inv_no'] ?></b></div>
                                    <div class="x-small text-muted">Amount: PKR <?= number_format($r['payable_amount'], 0) ?></div>
                                </td>
                                <td>
                                    <div class="small text-wrap" style="max-width: 200px;"><?= htmlspecialchars($r['description']) ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $badge = 'bg-secondary';
                                    if($r['status'] === 'approved') $badge = 'bg-success';
                                    if($r['status'] === 'rejected') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge rounded-pill <?= $badge ?> text-uppercase" style="font-size: 0.65rem;">
                                        <?= $r['status'] ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php if($r['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" 
                                            onclick="openProcessModal(<?= $r['id'] ?>, '<?= $r['student_name'] ?>', '<?= $r['request_type'] ?>')">
                                        Process
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-sm btn-outline-dark rounded-pill" disabled>Processed</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($requests)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">No requests found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Modal -->
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white border-0" style="border-radius: 20px 20px 0 0; padding: 20px;">
                <h5 class="modal-title fw-bold"><i class="bi bi-gear-wide-connected me-2"></i>Review Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="request_id" id="modal_req_id">
                
                <div class="mb-3 p-3 bg-light rounded-3 d-flex align-items-center">
                    <div class="flex-grow-1">
                        <small class="text-muted d-block text-uppercase fw-bold">Student Name</small>
                        <span id="modal_student_name" class="fw-bold text-primary"></span>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block text-uppercase fw-bold">Request Type</small>
                        <span id="modal_req_type" class="badge bg-dark"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Decision</label>
                    <select name="status" class="form-select border-0 bg-light py-2" required style="border-radius: 10px;">
                        <option value="approved">Approve Request</option>
                        <option value="rejected">Reject Request</option>
                    </select>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-muted text-uppercase">Internal Remarks / Response</label>
                    <textarea name="admin_remarks" class="form-control border-0 bg-light" rows="4" placeholder="Mention why this was approved/rejected (Visible to student)..." required style="border-radius: 10px;"></textarea>
                </div>

                <div id="automation_alert" class="alert alert-info border-0 mt-3 mb-0 small" style="display: none;">
                    <i class="bi bi-info-circle-fill me-2"></i> <b>System Note:</b> Approving this will automatically update the student's invoice.
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_request" class="btn btn-dark rounded-pill px-4 fw-bold">Submit Decision</button>
            </div>
        </form>
    </div>
</div>

<script>
function openProcessModal(reqId, name, type) {
    document.getElementById('modal_req_id').value = reqId;
    document.getElementById('modal_student_name').innerText = name;
    document.getElementById('modal_req_type').innerText = type.replace('_', ' ').toUpperCase();
    
    // Show automation hint for specific types
    const alert = document.getElementById('automation_alert');
    if(type === 'fine_waiver' || type === 'date_extension') {
        alert.style.display = 'block';
    } else {
        alert.style.display = 'none';
    }
    
    var myModal = new bootstrap.Modal(document.getElementById('processModal'));
    myModal.show();
}
</script>

<style>
.x-small { font-size: 0.7rem; }
.italic { font-style: italic; }
</style>

<?php require_once '../../includes/footer.php'; ?>
