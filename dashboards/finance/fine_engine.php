<?php 
require_once '../../includes/header.php'; 

// 1. Logic to Process Fines
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_engine'])) {
    $today = date('Y-m-d');
    
    // Fetch all unpaid invoices where due_date has passed
    $overdue = $pdo->query("
        SELECT i.*, f.late_fine_per_day, u.name as student_name, u.fee_category
        FROM invoices i 
        JOIN users u ON i.user_id = u.id
        JOIN fee_structures f ON (i.semester_id = f.semester_id AND u.fee_category = f.fee_category)
        WHERE i.status != 'paid' AND i.due_date < '$today'
    ")->fetchAll();

    $count = 0;
    $total_fine_added = 0;
    
    $pdo->beginTransaction();
    try {
        foreach ($overdue as $inv) {
            $fine_increment = $inv['late_fine_per_day'];
            $new_fine = $inv['fine_amount'] + $fine_increment;
            $new_payable = $inv['payable_amount'] + $fine_increment;
            $new_balance = $inv['balance_due'] + $fine_increment;
            
            $upd = $pdo->prepare("UPDATE invoices SET fine_amount = ?, payable_amount = ?, balance_due = ? WHERE id = ?");
            $upd->execute([$new_fine, $new_payable, $new_balance, $inv['id']]);
            
            $count++;
            $total_fine_added += $fine_increment;
        }
        $pdo->commit();
        $_SESSION['success_msg'] = "Automation Success! Applied penalties to <b>$count</b> overdue invoices. Total Fines: PKR " . number_format($total_fine_added, 0);
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = "Failed to run engine: " . $e->getMessage();
    }
    echo "<script>window.location.href='fine_engine.php';</script>";
    exit;
}

// Stats for the dashboard
$today = date('Y-m-d');
$overdue_count = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status != 'paid' AND due_date < '$today'")->fetchColumn();
$total_pending_fines = $pdo->query("SELECT SUM(fine_amount) FROM invoices")->fetchColumn() ?: 0;
?>

<style>
    .engine-card { border: none; border-radius: 20px; overflow: hidden; background: #fff; }
    .status-ring { width: 120px; height: 120px; border-radius: 50%; border: 8px solid #f8f9fa; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; transition: all 0.5s; }
    .status-ring.active { border-color: #ffc107; box-shadow: 0 0 20px rgba(255, 193, 7, 0.2); }
    .stat-pill { background: #f8f9fa; border-radius: 12px; padding: 15px; border: 1px solid #eee; }
    .pulse-warning { animation: pulse-yellow 2s infinite; }
    @keyframes pulse-yellow { 0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); } 70% { box-shadow: 0 0 0 15px rgba(255, 193, 7, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); } }
</style>

<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg'] ?>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card engine-card shadow-lg">
            <div class="row g-0">
                <div class="col-md-5 bg-warning bg-opacity-10 p-5 text-center d-flex flex-column justify-content-center">
                    <div class="status-ring active bg-white">
                        <i class="bi bi-shield-exclamation fs-1 text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Fine Automator</h4>
                    <p class="text-muted small">Daily Penalty Calculation System</p>
                    
                    <form method="POST" class="mt-4">
                        <button type="submit" name="run_engine" class="btn btn-warning btn-lg fw-bold rounded-pill px-5 shadow pulse-warning">
                            <i class="bi bi-lightning-charge-fill me-2"></i> Run Daily Engine
                        </button>
                    </form>
                    <p class="text-muted mt-3 x-small">* This will simulate one day of aging for all overdue invoices.</p>
                </div>
                <div class="col-md-7 p-5">
                    <h5 class="fw-bold mb-4">System Health & Preview</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="stat-pill">
                                <small class="text-muted d-block mb-1">Overdue Invoices</small>
                                <h3 class="fw-bold mb-0 text-danger"><?= $overdue_count ?></h3>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-pill">
                                <small class="text-muted d-block mb-1">Total Accumulated Fines</small>
                                <h3 class="fw-bold mb-0 text-dark">PKR <?= number_format($total_pending_fines, 0) ?></h3>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-uppercase small text-muted mb-3">Priority Preview (Top Overdue)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm border-top">
                            <tbody>
                                <?php
                                $preview = $pdo->query("
                                    SELECT u.name, i.payable_amount, i.due_date, f.late_fine_per_day
                                    FROM invoices i
                                    JOIN users u ON i.user_id = u.id
                                    JOIN fee_structures f ON (i.semester_id = f.semester_id AND u.fee_category = f.fee_category)
                                    WHERE i.status != 'paid' AND i.due_date < '$today'
                                    ORDER BY i.due_date ASC LIMIT 5
                                ")->fetchAll();

                                foreach($preview as $p):
                                    $days_late = ceil((time() - strtotime($p['due_date'])) / 86400);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold small"><?= htmlspecialchars($p['name']) ?></div>
                                        <div class="text-muted x-small">Due: <?= date('d M', strtotime($p['due_date'])) ?></div>
                                    </td>
                                    <td class="text-end">
                                        <div class="badge bg-danger-subtle text-danger small"><?= $days_late ?> days late</div>
                                        <div class="x-small text-muted">+PKR <?= $p['late_fine_per_day'] ?>/day</div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($preview)): ?>
                                <tr><td colspan="2" class="text-center py-4 text-muted small">All invoices are within due date.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>