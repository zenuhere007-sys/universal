<?php 
require_once '../../includes/header.php'; 

// Handle Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_program'])) {
    $name = trim($_POST['name']);
    $code = strtoupper(trim($_POST['code']));
    $dept_id = $_POST['department_id'];
    $total_sem = $_POST['total_semesters'] ?: 8;

    try {
        $stmt = $pdo->prepare("INSERT INTO programs (name, code, department_id, total_semesters) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $code, $dept_id, $total_sem]);
        $_SESSION['success_msg'] = "Degree <b>$name</b> added successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: Degree Code already exists or database issue.";
    }
    echo "<script>window.location.href='manage_programs.php';</script>";
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_program'])) {
    $id = $_POST['program_id'];
    $name = trim($_POST['name']);
    $code = strtoupper(trim($_POST['code']));
    $dept_id = $_POST['department_id'];
    $total_sem = $_POST['total_semesters'] ?: 8;

    try {
        $stmt = $pdo->prepare("UPDATE programs SET name = ?, code = ?, department_id = ?, total_semesters = ? WHERE id = ?");
        $stmt->execute([$name, $code, $dept_id, $total_sem, $id]);
        $_SESSION['success_msg'] = "Degree <b>$name</b> updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error updating degree: Code might already exist.";
    }
    echo "<script>window.location.href='manage_programs.php';</script>";
    exit;
}

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_program'])) {
    $id = $_POST['program_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "Degree deleted successfully.";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Constraint Error: Cannot delete a degree that has active semesters or students.";
    }
    echo "<script>window.location.href='manage_programs.php';</script>";
    exit;
}

// Fetch all programs with department info
$programs = $pdo->query("
    SELECT p.*, d.name as dept_name 
    FROM programs p 
    LEFT JOIN departments d ON p.department_id = d.id 
    ORDER BY p.name
")->fetchAll();

// Fetch departments for dropdown
$depts = $pdo->query("SELECT id, name FROM departments ORDER BY name")->fetchAll();
?>

<style>
    .status-badge { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; }
    .btn-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none; color: #fff; }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?></div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['error_msg'] ?></div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card glass-card shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">Academic Degrees (Programs)</h5>
                    <p class="text-muted small mb-0">Level 2 - Linking Degrees to Departments</p>
                </div>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-gradient" data-bs-toggle="modal" data-bs-target="#addProgModal">
                    <i class="bi bi-plus-lg me-2"></i> Add New Degree
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Degree Details</th>
                                <th>Code</th>
                                <th>Duration</th>
                                <th>Parent Department</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($programs as $p): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-box me-3"><i class="bi bi-mortarboard"></i></div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                            <small class="text-muted">Degree System</small>
                                        </div>
                                    </div>
                                </td>
                               <td><span class="badge bg-light text-dark border"><?= $p['code'] ?></span></td>
                                <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3"><?= $p['total_semesters'] ?> Semesters</span></td>
                                <td>
                                    <?php if($p['dept_name']): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2" style="border-radius: 10px;">
                                            <i class="bi bi-building me-1"></i> <?= htmlspecialchars($p['dept_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-icon text-primary border-0 me-2" 
                                            onclick="openEditModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name']) ?>', '<?= htmlspecialchars($p['code']) ?>', '<?= $p['department_id'] ?>', '<?= $p['total_semesters'] ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this degree program? Semesters and Course links will be lost.');">
                                        <input type="hidden" name="program_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="delete_program" class="btn btn-sm btn-icon text-danger border-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addProgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Register New Degree</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Degree Name</label>
                    <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="e.g. BS Software Engineering" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Degree Code</label>
                    <input type="text" name="code" class="form-control bg-light border-0 py-2" placeholder="e.g. BSSE" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Select Department</label>
                    <select name="department_id" class="form-select bg-light border-0 py-2" required style="border-radius: 10px;">
                        <option value="">-- Choose Parent --</option>
                        <?php foreach($depts as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="save_program" class="btn btn-primary rounded-pill px-4 btn-gradient">Save Degree</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editProgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Update Degree Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="program_id" id="edit_prog_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Degree Name</label>
                    <input type="text" name="name" id="edit_prog_name" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Degree Code</label>
                    <input type="text" name="code" id="edit_prog_code" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Select Department</label>
                    <select name="department_id" id="edit_prog_dept" class="form-select bg-light border-0 py-2" required style="border-radius: 10px;">
                        <option value="">-- Choose Parent --</option>
                        <?php foreach($depts as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Total Semesters</label>
                    <input type="number" name="total_semesters" id="edit_prog_sem" class="form-control bg-light border-0 py-2" min="1" max="12" required style="border-radius: 10px;">
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_program" class="btn btn-primary rounded-pill px-4 btn-gradient">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, code, deptId, totalSem) {
    document.getElementById('edit_prog_id').value = id;
    document.getElementById('edit_prog_name').value = name;
    document.getElementById('edit_prog_code').value = code;
    document.getElementById('edit_prog_dept').value = deptId;
    document.getElementById('edit_prog_sem').value = totalSem;
    var modal = new bootstrap.Modal(document.getElementById('editProgModal'));
    modal.show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>