<?php 
require_once '../../includes/header.php'; 

// Handle Department Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_dept'])) {
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);

    try {
        $stmt = $pdo->prepare("INSERT INTO departments (name, code) VALUES (?, ?)");
        $stmt->execute([$name, $code]);
        $_SESSION['success_msg'] = "Department <b>$name</b> created successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    echo "<script>window.location.href='manage_departments.php';</script>";
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_dept'])) {
    $id = $_POST['dept_id'];
    $name = trim($_POST['name']);
    $code = trim($_POST['code']);

    try {
        $stmt = $pdo->prepare("UPDATE departments SET name = ?, code = ? WHERE id = ?");
        $stmt->execute([$name, $code, $id]);
        $_SESSION['success_msg'] = "Department <b>$name</b> updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    echo "<script>window.location.href='manage_departments.php';</script>";
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_dept'])) {
    $id = $_POST['dept_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "Department deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Constraint Error: Cannot delete a department that has active degrees/programs.";
    }
    echo "<script>window.location.href='manage_departments.php';</script>";
    exit;
}

// Fetch all departments with degree counts
$depts = $pdo->query("
    SELECT d.*, (SELECT COUNT(*) FROM programs WHERE department_id = d.id) as degree_count
    FROM departments d
    ORDER BY d.name
")->fetchAll();
?>

<style>
    .status-badge { font-size: 0.75rem; padding: 4px 12px; border-radius: 20px; font-weight: 600; }
    .dept-icon { width: 45px; height: 45px; background: rgba(13, 110, 253, 0.1); color: #0d6efd; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    [data-bs-theme="dark"] .dept-icon { background: rgba(13, 110, 253, 0.2); color: #0d6efd; }
    .btn-gradient { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none; color: #fff; }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?></div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card glass-card shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0">University Departments</h5>
                    <p class="text-muted small mb-0">Level 1 of Academic Hierarchy</p>
                </div>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-gradient" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                    <i class="bi bi-plus-lg me-2"></i> Add Department
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Department Details</th>
                                <th>Code</th>
                                <th>Managed Degrees</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($depts as $d): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="dept-icon me-3"><i class="bi bi-building"></i></div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($d['name']) ?></div>
                                            <small class="text-muted">Established: <?= date('M Y', strtotime($d['created_at'])) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= $d['code'] ?></span></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2" style="border-radius: 10px;">
                                        <?= $d['degree_count'] ?> Programs
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-light border rounded-pill px-3 me-1" 
                                            onclick="openEditModal(<?= $d['id'] ?>, '<?= htmlspecialchars($d['name']) ?>', '<?= htmlspecialchars($d['code']) ?>')">
                                        Edit
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Really delete this department? All child settings might be affected.');">
                                        <input type="hidden" name="dept_id" value="<?= $d['id'] ?>">
                                        <button type="submit" name="delete_dept" class="btn btn-sm btn-outline-danger border rounded-pill px-3">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($depts)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-building-x fs-1 d-block mb-3"></i>
                                    No departments registered yet.
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

<!-- Add Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Create New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Department Name</label>
                    <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="e.g. Faculty of Computer Science" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Department Code</label>
                    <input type="text" name="code" class="form-control bg-light border-0 py-2" placeholder="e.g. FCS" required style="border-radius: 10px;">
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="save_dept" class="btn btn-primary rounded-pill px-4 btn-gradient">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Update Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="dept_id" id="edit_dept_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Department Name</label>
                    <input type="text" name="name" id="edit_dept_name" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Department Code</label>
                    <input type="text" name="code" id="edit_dept_code" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_dept" class="btn btn-primary rounded-pill px-4 btn-gradient">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, code) {
    document.getElementById('edit_dept_id').value = id;
    document.getElementById('edit_dept_name').value = name;
    document.getElementById('edit_dept_code').value = code;
    var modal = new bootstrap.Modal(document.getElementById('editDeptModal'));
    modal.show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>
