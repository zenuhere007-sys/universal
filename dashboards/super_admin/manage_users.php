<?php 
require_once '../../includes/header.php'; 

// Fetch Roles for Dropdown
$roles = $pdo->query("SELECT * FROM sys_roles")->fetchAll();
$programs = $pdo->query("SELECT * FROM programs ORDER BY name")->fetchAll();
$semesters = $pdo->query("SELECT s.*, p.name as prog_name FROM semesters s JOIN programs p ON s.program_id = p.id ORDER BY p.name, s.number")->fetchAll();

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $pass = password_hash('123456', PASSWORD_DEFAULT); // Default pass
    $program_id = (isset($_POST['program_id']) && $_POST['program_id'] !== '') ? $_POST['program_id'] : null;
    $semester_id = (isset($_POST['semester_id']) && $_POST['semester_id'] !== '') ? $_POST['semester_id'] : null;
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, role, password, program_id, semester_id) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $role, $pass, $program_id, $semester_id]);
        $_SESSION['success_msg'] = "User added successfully!";
    } catch(Exception $e) { 
        $_SESSION['error_msg'] = "Error: " . $e->getMessage(); 
    }
    echo "<script>window.location.href='manage_users.php';</script>";
    exit;
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $program_id = (isset($_POST['program_id']) && $_POST['program_id'] !== '') ? $_POST['program_id'] : null;
    $semester_id = (isset($_POST['semester_id']) && $_POST['semester_id'] !== '') ? $_POST['semester_id'] : null;
    
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, program_id = ?, semester_id = ? WHERE id = ?");
    try {
        $stmt->execute([$name, $email, $role, $program_id, $semester_id, $id]);
        $_SESSION['success_msg'] = "User updated successfully!";
    } catch(Exception $e) { 
        $_SESSION['error_msg'] = "Error updating user."; 
    }
    echo "<script>window.location.href='manage_users.php';</script>";
    exit;
}

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = $_POST['user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "User deleted successfully!";
    } catch(Exception $e) { 
        $_SESSION['error_msg'] = "Error deleting user."; 
    }
    echo "<script>window.location.href='manage_users.php';</script>";
    exit;
}

// Handle Toggle Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $id = $_POST['user_id'];
    $status = $_POST['current_status'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    $_SESSION['success_msg'] = "User status updated!";
    echo "<script>window.location.href='manage_users.php';</script>";
    exit;
}
?>

<div class="card glass-card shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h3 class="card-title fw-bold">User Management</h3>
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i> Add New User
        </button>
    </div>
    <div class="card-body p-0">
        <?php if(isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success m-3 border-0 shadow-sm small"><?= $_SESSION['success_msg'] ?></div>
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger m-3 border-0 shadow-sm small"><?= $_SESSION['error_msg'] ?></div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">User Details</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN sys_roles r ON u.role = r.role_key ORDER BY u.id DESC");
                    while($u = $users->fetch()):
                        $badgeClass = getRoleBadgeColor($u['role_name']); 
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                </div>
                                <div class="fw-bold"><?= htmlspecialchars($u['name']) ?></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($u['role_name']) ?></span></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $u['is_active'] ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm border-0 bg-transparent p-0">
                                    <?= $u['is_active'] ? '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Active</span>' : '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Suspended</span>' ?>
                                </button>
                            </form>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-icon text-primary border-0 me-2" 
                                    onclick="openEditModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', '<?= htmlspecialchars($u['email']) ?>', '<?= $u['role'] ?>', '<?= $u['program_id'] ?>', '<?= $u['semester_id'] ?>')">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Really delete this user?');">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="delete_user" class="btn btn-sm btn-icon text-danger border-0">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control bg-light border-0 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control bg-light border-0 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">System Role</label>
                    <select name="role" id="add_role_select" class="form-select bg-light border-0 py-2" required onchange="toggleStudentFields(this.value, 'add')">
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r['role_key'] ?>"><?= $r['role_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="add_student_fields" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Program</label>
                        <select name="program_id" class="form-select bg-light border-0 py-2">
                            <option value="">-- Select Program --</option>
                            <?php foreach($programs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Semester</label>
                        <select name="semester_id" class="form-select bg-light border-0 py-2">
                            <option value="">-- Select Semester --</option>
                            <?php foreach($semesters as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['prog_name'] ?> - Sem <?= $s['number'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <p class="text-muted smallest italic mt-2">Default password for new users is: <b>123456</b></p>
            </div>
            <div class="modal-footer border-0 p-4">
                <button type="submit" name="add_user" class="btn btn-primary rounded-pill px-4">Register User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Update User Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="name" id="edit_user_name" class="form-control bg-light border-0 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <input type="email" name="email" id="edit_user_email" class="form-control bg-light border-0 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">System Role</label>
                    <select name="role" id="edit_user_role" class="form-select bg-light border-0 py-2" required onchange="toggleStudentFields(this.value, 'edit')">
                        <?php foreach($roles as $r): ?>
                            <option value="<?= $r['role_key'] ?>"><?= $r['role_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="edit_student_fields" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Program</label>
                        <select name="program_id" id="edit_program_id" class="form-select bg-light border-0 py-2">
                            <option value="">-- Select Program --</option>
                            <?php foreach($programs as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Semester</label>
                        <select name="semester_id" id="edit_semester_id" class="form-select bg-light border-0 py-2">
                            <option value="">-- Select Semester --</option>
                            <?php foreach($semesters as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['prog_name'] ?> - Sem <?= $s['number'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>
            <div class="modal-footer border-0 p-4">
                <button type="submit" name="update_user" class="btn btn-primary rounded-pill px-4">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleStudentFields(role, type) {
    if(role === 'student') {
        document.getElementById(type + '_student_fields').style.display = 'block';
    } else {
        document.getElementById(type + '_student_fields').style.display = 'none';
    }
}

function openEditModal(id, name, email, role, program_id, semester_id) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_user_name').value = name;
    document.getElementById('edit_user_email').value = email;
    document.getElementById('edit_user_role').value = role;
    
    document.getElementById('edit_program_id').value = program_id || '';
    document.getElementById('edit_semester_id').value = semester_id || '';
    
    toggleStudentFields(role, 'edit');
    
    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

// Ensure proper display on load for Add modal if student happens to be selected initially
document.addEventListener('DOMContentLoaded', function() {
    toggleStudentFields(document.getElementById('add_role_select').value, 'add');
});
</script>

<?php require_once '../../includes/footer.php'; ?>