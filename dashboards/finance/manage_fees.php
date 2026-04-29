<?php 
require_once '../../includes/header.php'; 

try {
    // Categories to support
    $fee_categories = ['Regular', 'Self-Finance', 'Overseas', 'Scholarship-Basis'];

    // Handle Save/Update Fee Structure
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_fee'])) {
        $semester_id = $_POST['semester_id'];
        $category = $_POST['fee_category'];
        
        $fields = [
            'base_fee' => $_POST['base_fee'] ?: 0,
            'lab_charges' => $_POST['lab_charges'] ?: 0,
            'library_fee' => $_POST['library_fee'] ?: 0,
            'hostel_fee' => $_POST['hostel_fee'] ?: 0,
            'exam_fee' => $_POST['exam_fee'] ?: 0,
            'registration_fee' => $_POST['registration_fee'] ?: 0,
            'sports_fund' => $_POST['sports_fund'] ?: 0,
            'library_security' => $_POST['library_security'] ?: 0,
            'it_services' => $_POST['it_services'] ?: 0,
            'credit_hour_rate' => $_POST['credit_hour_rate'] ?: 0,
            'admission_fee' => $_POST['admission_fee'] ?: 0,
            'late_fine_per_day' => $_POST['late_fine_per_day'] ?: 0
        ];

        // Check if exists
        $check = $pdo->prepare("SELECT id FROM fee_structures WHERE semester_id = ? AND fee_category = ?");
        $check->execute([$semester_id, $category]);
        $existing = $check->fetch();

        if ($existing) {
            $set_parts = [];
            $values = [];
            foreach($fields as $k => $v) {
                $set_parts[] = "$k = ?";
                $values[] = $v;
            }
            $values[] = $existing['id'];
            $sql = "UPDATE fee_structures SET " . implode(', ', $set_parts) . " WHERE id = ?";
            $pdo->prepare($sql)->execute($values);
        } else {
            $cols = array_merge(['semester_id', 'fee_category'], array_keys($fields));
            $placeholders = array_fill(0, count($cols), '?');
            $values = array_merge([$semester_id, $category], array_values($fields));
            $sql = "INSERT INTO fee_structures (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $pdo->prepare($sql)->execute($values);
        }
        
        $_SESSION['success_msg'] = "Fee structure for <b>$category</b> updated successfully!";
        echo "<script>window.location.href='manage_fees.php';</script>";
        exit;
    }

    // Handle Reset/Delete Fee
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fee'])) {
        $id = $_POST['fee_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM fee_structures WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success_msg'] = "Fee structure removed successfully!";
        } catch (Exception $e) {
            $_SESSION['error_msg'] = "Error removing fee structure.";
        }
        echo "<script>window.location.href='manage_fees.php';</script>";
        exit;
    }

    // Fetch all semesters
    $semesters = $pdo->query("
        SELECT s.*, p.name as program_name 
        FROM semesters s 
        JOIN programs p ON s.program_id = p.id 
        ORDER BY p.name, s.number
    ")->fetchAll();

    // Group fee structures by semester for easier lookup
    $raw_structures = $pdo->query("SELECT * FROM fee_structures")->fetchAll();
    $structures = [];
    foreach($raw_structures as $rs) {
        if(!isset($structures[$rs['semester_id']])) $structures[$rs['semester_id']] = [];
        $structures[$rs['semester_id']][$rs['fee_category']] = $rs;
    }

    // Fetch total credit hours per program and semester for automatic calculation
    $ch_map = [];
    try {
        $ch_summaries = $pdo->query("
            SELECT program_id, semester_no, SUM(credit_hours) as total_ch 
            FROM courses 
            GROUP BY program_id, semester_no
        ")->fetchAll();
        foreach($ch_summaries as $cs) {
            if(!isset($ch_map[$cs['program_id']])) $ch_map[$cs['program_id']] = [];
            $ch_map[$cs['program_id']][$cs['semester_no']] = (int)$cs['total_ch'];
        }
    } catch (Exception $e) {
        // Fallback if courses table integration has issues
    }

} catch (Exception $e) {
    echo "<div class='alert alert-danger m-4'>Error: " . $e->getMessage() . "</div>";
    require_once '../../includes/footer.php';
    exit;
}
?>

<style>
    .fee-card { transition: transform 0.2s, box-shadow 0.2s; border: none; border-radius: 12px; }
    .fee-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .category-badge { font-size: 0.75rem; padding: 4px 8px; border-radius: 20px; }
    .modal-content { border-radius: 15px; border: none; overflow: hidden; }
    .modal-header { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white; border: none; }
    .glass-input { border-radius: 8px; border: 1px solid #dee2e6; transition: border-color 0.2s; }
    .glass-input:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.15); }
    .btn-premium { border-radius: 8px; padding: 8px 16px; font-weight: 500; transition: all 0.2s; }
    .table-premium thead { background-color: #f8f9fa; border-top: 2px solid #dee2e6; }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if(empty($semesters)): ?>
    <div class="card shadow-sm border-0 text-center py-5" style="border-radius: 15px;">
        <div class="card-body">
            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
            <h4 class="fw-bold">No Semesters Found</h4>
            <p class="text-muted">Please add degrees and semesters first in the Academic Setup section.</p>
            <a href="../super_admin/manage_semesters.php" class="btn btn-primary rounded-pill px-4">Go to Setup</a>
        </div>
    </div>
<?php else: ?>

<div class="row g-4">
    <?php foreach($semesters as $s): ?>
    <div class="col-xl-6">
        <div class="card fee-card h-100 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3">
                <div>
                    <h5 class="mb-1 text-primary fw-bold"><?= htmlspecialchars($s['program_name'] ?? 'N/A') ?></h5>
                    <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> <?= htmlspecialchars($s['name']) ?> (Semester <?= $s['number'] ?>)</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg me-1"></i> Setup Fees
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <?php foreach($fee_categories as $cat): ?>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?><?= str_replace(' ', '', str_replace('-', '', $cat)) ?>">
                            <i class="bi bi-layers-fill me-2 text-secondary"></i> <?= $cat ?>
                        </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-premium table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Category</th>
                                <th>Base Fee</th>
                                <th>Total Est.</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $defined_any = false;
                            foreach($fee_categories as $cat): 
                                $f = $structures[$s['id']][$cat] ?? null;
                                if($f):
                                    $defined_any = true;
                                    $total = $f['admission_fee'] + $f['base_fee'] + $f['lab_charges'] + $f['library_fee'] + $f['hostel_fee'] + $f['exam_fee'] + $f['registration_fee'] + $f['sports_fund'] + $f['library_security'] + $f['it_services'];
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-semibold"><?= $cat ?></span>
                                </td>
                                <td>PKR <?= number_format($f['base_fee'], 0) ?></td>
                                <td class="fw-bold text-success font-monospace">PKR <?= number_format($total, 0) ?></td>
                                <td class="text-center">
                                    <span class="badge category-badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-icon border-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?><?= str_replace(' ', '', str_replace('-', '', $cat)) ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Reset this fee structure? This will clear all defined amounts for this category.');">
                                        <input type="hidden" name="fee_id" value="<?= $f['id'] ?>">
                                        <button type="submit" name="delete_fee" class="btn btn-sm btn-icon border-0 text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endif; endforeach; ?>
                            
                            <?php if(!$defined_any): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> No fee structures defined yet.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals for this semester -->
    <?php foreach($fee_categories as $cat): 
        $f = $structures[$s['id']][$cat] ?? null;
        $cat_key = str_replace(' ', '', str_replace('-', '', $cat));
    ?>
    <div class="modal fade" id="editModal<?= $s['id'] ?><?= $cat_key ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="POST" class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-sliders me-2"></i> Configure Fees: <?= $cat ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="semester_id" value="<?= $s['id'] ?>">
                    <input type="hidden" name="fee_category" value="<?= $cat ?>">
                    
                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2 mb-3">Academic Info</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Program</label>
                                <input type="text" class="form-control-plaintext ps-2" readonly value="<?= htmlspecialchars($s['program_name'] ?? 'N/A') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Semester</label>
                                <input type="text" class="form-control-plaintext ps-2" readonly value="<?= htmlspecialchars($s['name']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2 mb-3">Core Fees (Mandatory)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Admission Fee (1st Sem)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">PKR</span>
                                    <input type="number" name="admission_fee" class="form-control glass-input" value="<?= $f['admission_fee'] ?? 0 ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Base Tuition Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">PKR</span>
                                    <input type="number" name="base_fee" id="base_fee_<?= $s['id'] ?>_<?= $cat_key ?>" class="form-control glass-input" value="<?= $f['base_fee'] ?? 0 ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Registration Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">PKR</span>
                                    <input type="number" name="registration_fee" class="form-control glass-input" value="<?= $f['registration_fee'] ?? 0 ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Exam Fee</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">PKR</span>
                                    <input type="number" name="exam_fee" class="form-control glass-input" value="<?= $f['exam_fee'] ?? 0 ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2 mb-3">Facility & Services</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Lab Charges</label>
                                <input type="number" name="lab_charges" class="form-control glass-input" value="<?= $f['lab_charges'] ?? 0 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Library Fee</label>
                                <input type="number" name="library_fee" class="form-control glass-input" value="<?= $f['library_fee'] ?? 0 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">IT Services</label>
                                <input type="number" name="it_services" class="form-control glass-input" value="<?= $f['it_services'] ?? 0 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Sports Fund</label>
                                <input type="number" name="sports_fund" class="form-control glass-input" value="<?= $f['sports_fund'] ?? 0 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Hostel Fee</label>
                                <input type="number" name="hostel_fee" class="form-control glass-input" value="<?= $f['hostel_fee'] ?? 0 ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Lib. Security (Refundable)</label>
                                <input type="number" name="library_security" class="form-control glass-input" value="<?= $f['library_security'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-uppercase text-muted fw-bold small border-bottom pb-2 mb-3">Rules & Dynamic Rates</h6>
                        <?php $total_ch = $ch_map[$s['program_id']][$s['number']] ?? 0; ?>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Credit Hour Rate</label>
                                <input type="number" name="credit_hour_rate" id="ch_rate_<?= $s['id'] ?>_<?= $cat_key ?>" 
                                       class="form-control glass-input" value="<?= $f['credit_hour_rate'] ?? 0 ?>"
                                       oninput="calcRate(<?= $s['id'] ?>, '<?= $cat_key ?>', <?= $total_ch ?>)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Total Credits</label>
                                <input type="text" class="form-control-plaintext ps-2 fw-bold text-primary" readonly value="<?= $total_ch ?> CH">
                                <small class="text-muted" style="font-size: 0.65rem;">Linked from Course Catalog</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Late Fine (Daily)</label>
                                <input type="number" name="late_fine_per_day" class="form-control glass-input" value="<?= $f['late_fine_per_day'] ?? 0 ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <div class="me-auto ps-2">
                        <span class="small text-muted">Auto-Tuition:</span>
                        <span id="calc_display_<?= $s['id'] ?>_<?= $cat_key ?>" class="fw-bold text-success ms-1">
                            PKR <?= number_format($total_ch * ($f['credit_hour_rate'] ?? 0)) ?>
                        </span>
                    </div>
                    <button type="button" class="btn btn-premium btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_fee" class="btn btn-premium btn-primary">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <?php endforeach; ?>
</div>

<script>
function calcRate(semId, catKey, totalCH) {
    const rateInput = document.getElementById(`ch_rate_${semId}_${catKey}`);
    const display = document.getElementById(`calc_display_${semId}_${catKey}`);
    const baseInput = document.getElementById(`base_fee_${semId}_${catKey}`);
    
    const rate = parseFloat(rateInput.value) || 0;
    const total = rate * totalCH;
    
    display.innerText = 'PKR ' + total.toLocaleString();
    if(rate > 0) {
        baseInput.value = total;
        baseInput.style.borderColor = "#198754";
        baseInput.style.backgroundColor = "rgba(25, 135, 84, 0.05)";
    } else {
        baseInput.style.borderColor = "#dee2e6";
        baseInput.style.backgroundColor = "#fff";
    }
}
</script>

<?php endif; ?>

<?php require_once '../../includes/footer.php'; ?>
