<?php 
require_once '../../includes/header.php'; 

// 1. Handle Add/Update Scholarship Definition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_scholarship'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $amount = $_POST['amount'];
    
    $stmt = $pdo->prepare("INSERT INTO scholarships (name, type, amount) VALUES (?, ?, ?)");
    $stmt->execute([$name, $type, $amount]);
    $_SESSION['success_msg'] = "New scholarship <b>$name</b> defined successfully!";
    echo "<script>window.location.href='manage_scholarships.php';</script>";
    exit;
}

// 2. Handle Awarding/Assigning Scholarship to Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['award_scholarship'])) {
    $user_id = $_POST['user_id'];
    $scholar_id = $_POST['scholarship_id'];
    
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_scholarships (user_id, scholarship_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $scholar_id]);
        
        // Fetch scholarship details to update user record
        $sch = $pdo->prepare("SELECT * FROM scholarships WHERE id = ?");
        $sch->execute([$scholar_id]);
        $s_data = $sch->fetch();
        
        if ($s_data['type'] === 'percentage') {
            $upd = $pdo->prepare("UPDATE users SET scholarship_percent = ?, scholarship_fixed = 0 WHERE id = ?");
            $upd->execute([$s_data['amount'], $user_id]);
        } else {
            $upd = $pdo->prepare("UPDATE users SET scholarship_fixed = ?, scholarship_percent = 0 WHERE id = ?");
            $upd->execute([$s_data['amount'], $user_id]);
        }
        
        // --- AUTO RE-SYNC INVOICES ---
        // Find existing unpaid academic invoices for this student
        $invStmt = $pdo->prepare("SELECT * FROM invoices WHERE user_id = ? AND status = 'unpaid' AND invoice_type = 'academic'");
        $invStmt->execute([$user_id]);
        $active_invoices = $invStmt->fetchAll();
        
        foreach($active_invoices as $inv) {
            $discount = 0;
            if ($s_data['type'] === 'percentage') {
                $discount = ($inv['total_base_amount'] * $s_data['amount']) / 100;
            } else {
                $discount = $s_data['amount'];
            }
            
            $payable = max(0, $inv['total_base_amount'] - $discount);
            
            // Update Invoice
            $updInv = $pdo->prepare("UPDATE invoices SET discount_amount = ?, payable_amount = ?, balance_due = ? WHERE id = ?");
            $updInv->execute([$discount, $payable, $payable, $inv['id']]);
            
            // If there's an installment plan, recalculate it
            $instCountStmt = $pdo->prepare("SELECT COUNT(*) FROM installments WHERE invoice_id = ?");
            $instCountStmt->execute([$inv['id']]);
            $num_inst = $instCountStmt->fetchColumn();
            
            if ($num_inst > 0) {
                // Fetch installment details to keep count and interval
                $all_inst = $pdo->prepare("SELECT * FROM installments WHERE invoice_id = ? ORDER BY installment_no");
                $all_inst->execute([$inv['id']]);
                $inst_rows = $all_inst->fetchAll();
                
                $amount_per_inst = round($payable / $num_inst);
                foreach($inst_rows as $idx => $row) {
                    $current_amount = $amount_per_inst;
                    if ($idx == $num_inst - 1) { // last one
                        $current_amount = $payable - ($amount_per_inst * ($num_inst - 1));
                    }
                    $updInst = $pdo->prepare("UPDATE installments SET amount = ? WHERE id = ?");
                    $updInst->execute([$current_amount, $row['id']]);
                }
            }
        }
        
        $pdo->commit();
        $_SESSION['success_msg'] = "Scholarship awarded and invoices/installments synchronized!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    echo "<script>window.location.href='manage_scholarships.php';</script>";
    exit;
}

// 3. Handle Removing Award
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_award'])) {
    $user_id = $_POST['user_id'];
    $scholar_id = $_POST['scholar_id'];
    
    $pdo->beginTransaction();
    try {
        // Delete assignment
        $stmt = $pdo->prepare("DELETE FROM user_scholarships WHERE user_id = ? AND scholarship_id = ?");
        $stmt->execute([$user_id, $scholar_id]);
        
        // Reset user record
        $updUser = $pdo->prepare("UPDATE users SET scholarship_percent = 0, scholarship_fixed = 0 WHERE id = ?");
        $updUser->execute([$user_id]);
        
        // --- AUTO RE-SYNC INVOICES (Back to Full Amount) ---
        $invStmt = $pdo->prepare("SELECT * FROM invoices WHERE user_id = ? AND status = 'unpaid' AND invoice_type = 'academic'");
        $invStmt->execute([$user_id]);
        $active_invoices = $invStmt->fetchAll();
        
        foreach($active_invoices as $inv) {
            $payable = $inv['total_base_amount']; // No discount
            
            // Update Invoice
            $updInv = $pdo->prepare("UPDATE invoices SET discount_amount = 0, payable_amount = ?, balance_due = ? WHERE id = ?");
            $updInv->execute([$payable, $payable, $inv['id']]);
            
            // Recalculate Installments if any
            $instCountStmt = $pdo->prepare("SELECT COUNT(*) FROM installments WHERE invoice_id = ?");
            $instCountStmt->execute([$inv['id']]);
            $num_inst = $instCountStmt->fetchColumn();
            
            if ($num_inst > 0) {
                $all_inst = $pdo->prepare("SELECT * FROM installments WHERE invoice_id = ? ORDER BY installment_no");
                $all_inst->execute([$inv['id']]);
                $inst_rows = $all_inst->fetchAll();
                
                $amount_per_inst = round($payable / $num_inst);
                foreach($inst_rows as $idx => $row) {
                    $current_amount = $amount_per_inst;
                    if ($idx == $num_inst - 1) { // last one
                        $current_amount = $payable - ($amount_per_inst * ($num_inst - 1));
                    }
                    $updInst = $pdo->prepare("UPDATE installments SET amount = ? WHERE id = ?");
                    $updInst->execute([$current_amount, $row['id']]);
                }
            }
        }
        
        $pdo->commit();
        $_SESSION['success_msg'] = "Scholarship award removed and invoices reset.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    echo "<script>window.location.href='manage_scholarships.php';</script>";
    exit;
}

// Fetch all scholarship types
$scholarships = $pdo->query("SELECT * FROM scholarships ORDER BY name")->fetchAll();

// Fetch currently awarded scholarships
$awards = $pdo->query("
    SELECT u.id as user_id, u.name, u.roll_no, u.scholarship_percent, u.scholarship_fixed, 
           s.id as scholarship_id, s.name as scholarship_name
    FROM users u
    JOIN user_scholarships us ON u.id = us.user_id
    JOIN scholarships s ON us.scholarship_id = s.id
    ORDER BY u.name
")->fetchAll();
?>

<style>
    .status-badge { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; }
    .sch-icon { width: 45px; height: 45px; background: rgba(13, 110, 253, 0.1); color: #0d6efd; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    [data-bs-theme="dark"] .sch-icon { background: rgba(13, 110, 253, 0.2); }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="row g-4 mb-4">
    <!-- Define Scholarship -->
    <div class="col-xl-4 col-lg-5">
        <div class="card glass-card shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-1"><i class="bi bi-patch-check-fill text-info me-2"></i> Scholarship Catalog</h5>
                <p class="text-muted small">Create and manage scholarship types</p>
            </div>
            <div class="card-body px-4">
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Scheme Name</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="e.g. Dean's Honor Roll" required style="border-radius: 10px;">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-bold">Type</label>
                            <select name="type" class="form-select bg-light border-0 py-2" style="border-radius: 10px;">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (PKR)</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-bold">Value</label>
                            <input type="number" name="amount" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                        </div>
                    </div>
                    <button type="submit" name="save_scholarship" class="btn btn-info w-100 fw-bold py-2 text-white shadow-sm" style="border-radius: 10px;">
                        Add to Catalog
                    </button>
                </form>

                <div class="list-group list-group-flush border-top pt-3">
                    <?php foreach($scholarships as $s): ?>
                    <div class="list-group-item bg-transparent px-0 border-0 mb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($s['name']) ?></div>
                                <small class="text-muted"><?= $s['type'] == 'percentage' ? 'Percentage Relief' : 'Direct Credit' ?></small>
                            </div>
                            <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                <?= number_format($s['amount'], 0) ?><?= $s['type'] == 'percentage' ? '%' : ' PKR' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Award Scholarship -->
    <div class="col-xl-8 col-lg-7">
        <div class="card glass-card shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i> Active Scholarship Awards</h5>
                        <p class="text-muted small">Manage student assignments and relief</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#awardModal">
                        <i class="bi bi-plus-lg me-1"></i> New Award
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="ps-4 border-0">Student Details</th>
                                <th class="border-0">Award Name</th>
                                <th class="border-0">Total Relief</th>
                                <th class="text-end pe-4 border-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($awards as $a): ?>
                            <tr class="award-row">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box me-3"><i class="bi bi-person"></i></div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($a['name']) ?></div>
                                            <div class="text-muted small"><?= $a['roll_no'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($a['scholarship_name']) ?></span>
                                </td>
                                <td>
                                    <?php if($a['scholarship_percent'] > 0): ?>
                                        <span class="badge sch-badge bg-success-subtle text-success border border-success-subtle">
                                            <?= number_format($a['scholarship_percent'], 0) ?>% Waiver
                                        </span>
                                    <?php else: ?>
                                        <span class="badge sch-badge bg-purple-subtle text-purple border border-purple-subtle" style="background-color: #f3f0ff; color: #7048e8; border-color: #d8d2f9;">
                                            PKR <?= number_format($a['scholarship_fixed'], 0) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <form method="POST" onsubmit="return confirm('Remove this scholarship award and reset student invoices?');">
                                        <input type="hidden" name="user_id" value="<?= $a['user_id'] ?>">
                                        <input type="hidden" name="scholar_id" value="<?= $a['scholarship_id'] ?>">
                                        <button type="submit" name="remove_award" class="btn btn-sm btn-icon text-danger border-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($awards)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                                    No scholarships awarded yet.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Award Modal -->
<div class="modal fade" id="awardModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header gradient-header" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-trophy me-2"></i> Award Scholarship</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">1. Filter Program</label>
                    <?php $progs = $pdo->query("SELECT * FROM programs ORDER BY name")->fetchAll(); ?>
                    <select id="schFilterProg" class="form-select border-0 bg-light py-2" style="border-radius: 10px;">
                        <option value="">-- All Programs --</option>
                        <?php foreach($progs as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">2. Search Student</label>
                    <input type="text" id="schStuQuery" class="form-control border-0 bg-light py-2" placeholder="Start typing name..." style="border-radius: 10px;">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">3. Choose Student</label>
                    <select name="user_id" id="schStuSelect" class="form-select border-0 bg-light py-2" required style="border-radius: 10px;">
                        <option value="">-- No matches --</option>
                    </select>
                </div>

                <script>
                    const schProg = document.getElementById('schFilterProg');
                    const schQuery = document.getElementById('schStuQuery');
                    const schSelect = document.getElementById('schStuSelect');

                    function loadAwardStudents() {
                        const pid = schProg.value;
                        const q = schQuery.value;
                        
                        fetch(`../../core/search_students.php?type=student&program_id=${pid}&query=${q}`)
                            .then(res => res.json())
                            .then(data => {
                                schSelect.innerHTML = '<option value="">-- Select Student --</option>';
                                data.forEach(st => {
                                    const opt = document.createElement('option');
                                    opt.value = st.id;
                                    opt.textContent = `${st.roll_no} - ${st.name} (${st.program_name})`;
                                    schSelect.appendChild(opt);
                                });
                            });
                    }

                    schProg.onchange = loadAwardStudents;
                    schQuery.onkeyup = loadAwardStudents;
                    loadAwardStudents(); // Init
                </script>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Scholarship Scheme</label>
                    <select name="scholarship_id" class="form-select border-0 bg-light py-2" required style="border-radius: 10px;">
                        <option value="">-- Choose Scheme --</option>
                        <?php foreach($scholarships as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= number_format($s['amount'], 0) ?><?= $s['type']=='percentage'?'%':' PKR' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alert alert-info border-0 small mb-0">
                    <i class="bi bi-info-circle-fill me-2"></i> Awarding a scholarship will automatically recalculate new invoices for this student.
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="award_scholarship" class="btn btn-primary rounded-pill px-4 fw-bold">Assign Now</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
