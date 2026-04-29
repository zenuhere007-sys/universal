<?php 
require_once '../../includes/header.php'; 

// Handle Course Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    $name = trim($_POST['name']);
    $code = strtoupper(trim($_POST['code']));
    $prog_id = $_POST['program_id'];
    $ch = $_POST['credit_hours'];
    $sem_no = $_POST['semester_no'];

    try {
        $stmt = $pdo->prepare("INSERT INTO courses (name, code, program_id, credit_hours, semester_no) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $code, $prog_id, $ch, $sem_no]);
        $_SESSION['success_msg'] = "Course <b>$name</b> added successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error: Course Code already exists or database issue.";
    }
    echo "<script>window.location.href='manage_courses.php';</script>";
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $id = $_POST['course_id'];
    $name = trim($_POST['name']);
    $code = strtoupper(trim($_POST['code']));
    $prog_id = $_POST['program_id'];
    $ch = $_POST['credit_hours'];
    $sem_no = $_POST['semester_no'];

    try {
        $stmt = $pdo->prepare("UPDATE courses SET name = ?, code = ?, program_id = ?, credit_hours = ?, semester_no = ? WHERE id = ?");
        $stmt->execute([$name, $code, $prog_id, $ch, $sem_no, $id]);
        $_SESSION['success_msg'] = "Course <b>$name</b> updated successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Error updating course: Code might already exist.";
    }
    echo "<script>window.location.href='manage_courses.php';</script>";
    exit;
}

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    $id = $_POST['course_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "Course deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Constraint Error: Cannot delete a course linked to fees or student records.";
    }
    echo "<script>window.location.href='manage_courses.php';</script>";
    exit;
}

// Filter Logic
$prog_filter = $_GET['prog_id'] ?? '';
$where = $prog_filter ? "WHERE c.program_id = ?" : "";
$params = $prog_filter ? [$prog_filter] : [];

// Fetch courses
$sql = "
    SELECT c.*, p.name as prog_name 
    FROM courses c 
    JOIN programs p ON c.program_id = p.id 
    $where
    ORDER BY p.name, c.semester_no, c.name
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$courses = $stmt->fetchAll();

// Fetch programs for dropdown
$programs = $pdo->query("SELECT id, name FROM programs ORDER BY name")->fetchAll();
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
        <div class="card glass-card shadow-sm mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <label class="fw-bold small text-muted text-uppercase mb-0">Filter by Degree:</label>
                    <select name="prog_id" class="form-select border-0 bg-light py-2" style="border-radius: 10px; width: 250px;" onchange="this.form.submit()">
                        <option value="">-- All Degrees --</option>
                        <?php foreach($programs as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $prog_filter == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <button class="btn btn-primary rounded-pill px-4 shadow-sm btn-gradient" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                    <i class="bi bi-journal-plus me-2"></i> Add New Course
                </button>
            </div>
        </div>

        <div class="card glass-card shadow-sm">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Course Catalog</h5>
                <p class="text-muted small mb-0">Level 3 - Detailed Course Distribution</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Course Name & Code</th>
                                <th>Mapped Degree</th>
                                <th>Semester</th>
                                <th>Credit Hours</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($courses as $c): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="course-icon me-3"><i class="bi bi-journal-text"></i></div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></div>
                                            <small class="badge bg-light text-muted border"><?= $c['code'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-3 py-2" style="border-radius: 10px; color: #6f42c1; background-color: #f3f0ff;">
                                        <i class="bi bi-mortarboard me-1"></i> <?= htmlspecialchars($c['prog_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border px-3">Sem <?= $c['semester_no'] ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold">0<?= $c['credit_hours'] ?></span> <small class="text-muted">CH</small>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-icon text-primary border-0 me-2" 
                                            onclick="openEditModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['name']) ?>', '<?= htmlspecialchars($c['code']) ?>', '<?= $c['program_id'] ?>', '<?= $c['credit_hours'] ?>', '<?= $c['semester_no'] ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course?');">
                                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                        <button type="submit" name="delete_course" class="btn btn-sm btn-icon text-danger border-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($courses)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
                                    No courses found for this selection.
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
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Register New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Course Title</label>
                    <input type="text" name="name" class="form-control bg-light border-0 py-2" placeholder="e.g. Data Structures & Algorithms" required style="border-radius: 10px;">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label small fw-bold">Course Code</label>
                        <input type="text" name="code" class="form-control bg-light border-0 py-2" placeholder="e.g. CS-201" required style="border-radius: 10px;">
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-bold">Credit Hours</label>
                        <input type="number" name="credit_hours" class="form-control bg-light border-0 py-2" value="3" min="1" max="6" style="border-radius: 10px;">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label small fw-bold">Link to Degree</label>
                        <select name="program_id" class="form-select bg-light border-0 py-2" required style="border-radius: 10px;">
                            <option value="">-- Select Program --</option>
                            <?php foreach($programs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-bold">Semester No.</label>
                        <input type="number" name="semester_no" class="form-control bg-light border-0 py-2" value="1" min="1" max="10" required style="border-radius: 10px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="save_course" class="btn btn-success rounded-pill px-4 btn-gradient">Add Course</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Update Course Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="course_id" id="edit_course_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Course Title</label>
                    <input type="text" name="name" id="edit_course_name" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label small fw-bold">Course Code</label>
                        <input type="text" name="code" id="edit_course_code" class="form-control bg-light border-0 py-2" required style="border-radius: 10px;">
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-bold">Credit Hours</label>
                        <input type="number" name="credit_hours" id="edit_course_ch" class="form-control bg-light border-0 py-2" min="1" max="6" style="border-radius: 10px;">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label small fw-bold">Link to Degree</label>
                        <select name="program_id" id="edit_course_prog" class="form-select bg-light border-0 py-2" required style="border-radius: 10px;">
                            <option value="">-- Select Program --</option>
                            <?php foreach($programs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label small fw-bold">Semester No.</label>
                        <input type="number" name="semester_no" id="edit_course_sem" class="form-control bg-light border-0 py-2" min="1" max="10" required style="border-radius: 10px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_course" class="btn btn-primary rounded-pill px-4 btn-gradient">Update Course</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, code, progId, ch, sem) {
    document.getElementById('edit_course_id').value = id;
    document.getElementById('edit_course_name').value = name;
    document.getElementById('edit_course_code').value = code;
    document.getElementById('edit_course_prog').value = progId;
    document.getElementById('edit_course_ch').value = ch;
    document.getElementById('edit_course_sem').value = sem;
    var modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    modal.show();
}
</script>

<?php require_once '../../includes/footer.php'; ?>
