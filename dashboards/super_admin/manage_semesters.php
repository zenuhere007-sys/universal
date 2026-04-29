<?php 
require_once '../../includes/header.php'; 

// Handle Add Semester
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_semester'])) {
    $program_id = $_POST['program_id'];
    $name = trim($_POST['name']);
    $number = $_POST['number'];
    
    $stmt = $pdo->prepare("INSERT INTO semesters (program_id, name, number) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$program_id, $name, $number]);
        $_SESSION['success_msg'] = "Semester <b>$name</b> added successfully!";
    } catch(Exception $e) { 
        $_SESSION['error_msg'] = "Error adding semester: " . $e->getMessage(); 
    }
    echo "<script>window.location.href='manage_semesters.php';</script>";
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_semester'])) {
    $id = $_POST['semester_id'];
    $program_id = $_POST['program_id'];
    $name = trim($_POST['name']);
    $number = $_POST['number'];
    
    try {
        $stmt = $pdo->prepare("UPDATE semesters SET program_id = ?, name = ?, number = ? WHERE id = ?");
        $stmt->execute([$program_id, $name, $number, $id]);
        $_SESSION['success_msg'] = "Semester <b>$name</b> updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error updating semester.";
    }
    echo "<script>window.location.href='manage_semesters.php';</script>";
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_semester'])) {
    $id = $_POST['semester_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM semesters WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "Semester deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Constraint Error: Cannot delete a semester that has active courses or student records.";
    }
    echo "<script>window.location.href='manage_semesters.php';</script>";
    exit;
}

// Fetch programs for dropdown
$programs = $pdo->query("SELECT * FROM programs ORDER BY name")->fetchAll();

// Fetch all semesters with program name
$semesters = $pdo->query("
    SELECT s.*, p.name as program_name 
    FROM semesters s 
    JOIN programs p ON s.program_id = p.id 
    ORDER BY p.name, s.number
")->fetchAll();
?>

<div class="card glass-card shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h3 class="card-title">Manage Semesters</h3>
        <button class="btn btn-primary btn-sm ms-auto" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-1"></i> Add New Semester
        </button>
    </div>
    <div class="card-body p-0">
<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['error_msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Program</th>
                        <th>Semester Name</th>
                        <th>Semester #</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($semesters as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['program_name']) ?></strong></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><span class="badge bg-secondary"><?= $s['number'] ?></span></td>
                        <td><span class="badge bg-info"><?= ucfirst($s['status']) ?></span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary border me-1" 
                                    onclick="openEditModal(<?= $s['id'] ?>, '<?= $s['program_id'] ?>', '<?= htmlspecialchars($s['name']) ?>', '<?= $s['number'] ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this semester?');">
                                <input type="hidden" name="semester_id" value="<?= $s['id'] ?>">
                                <button type="submit" name="delete_semester" class="btn btn-sm btn-outline-danger border">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($semesters)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No semesters defined yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Target Program</label>
                    <select name="program_id" class="form-select" required>
                        <option value="">-- Select Program --</option>
                        <?php foreach($programs as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['name'] ?> (<?= $p['code'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Fall 2024" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester Number</label>
                    <input type="number" name="number" class="form-control" min="1" max="12" value="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_semester" class="btn btn-primary">Save Semester</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Semester Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="semester_id" id="edit_semester_id">
                <div class="mb-3">
                    <label class="form-label">Target Program</label>
                    <select name="program_id" id="edit_program_id" class="form-select" required>
                        <option value="">-- Select Program --</option>
                        <?php foreach($programs as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['name'] ?> (<?= $p['code'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" placeholder="e.g. Fall 2024" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Semester Number</label>
                    <input type="number" name="number" id="edit_number" class="form-control" min="1" max="12" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_semester" class="btn btn-primary">Update Semester</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, progId, name, num) {
    document.getElementById('edit_semester_id').value = id;
    document.getElementById('edit_program_id').value = progId;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_number').value = num;
    var modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>