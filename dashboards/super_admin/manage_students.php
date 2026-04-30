<?php 
require_once '../../includes/header.php'; 

// Fetch Students with Department, Program, and Semester
$query = "
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.roll_no,
        p.name as program_name, 
        d.name as department_name, 
        s.name as semester_name,
        s.number as semester_number
    FROM users u
    LEFT JOIN programs p ON u.program_id = p.id
    LEFT JOIN departments d ON p.department_id = d.id
    LEFT JOIN semesters s ON u.semester_id = s.id
    WHERE u.role = 'student'
    ORDER BY d.name, p.name, s.number, u.name
";
$students = $pdo->query($query)->fetchAll();
?>

<div class="card glass-card shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h3 class="card-title fw-bold">Students Directory</h3>
        <button class="btn btn-outline-secondary rounded-pill px-4" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print List
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Student Details</th>
                        <th>Roll No</th>
                        <th>Department</th>
                        <th>Program</th>
                        <th>Semester</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">
                            <i class="bi bi-info-circle me-1"></i> No students found in the system.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach($students as $st): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                                        <?= strtoupper(substr($st['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($st['name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($st['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                    <?= htmlspecialchars($st['roll_no'] ?: 'N/A') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($st['department_name'] ?: 'Not Assigned') ?></td>
                            <td><?= htmlspecialchars($st['program_name'] ?: 'Not Assigned') ?></td>
                            <td>
                                <?php if ($st['semester_name']): ?>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <?= htmlspecialchars($st['semester_name']) ?> (Sem <?= htmlspecialchars($st['semester_number']) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Not Assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
