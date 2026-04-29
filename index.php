<?php 
require_once 'includes/header.php'; 

// --- Data Fetching ---
// Global Stats
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$myPerms = $pdo->prepare("SELECT COUNT(*) FROM role_access WHERE role_key = ?");
$myPerms->execute([$_SESSION['role']]);
$myPermCount = $myPerms->fetchColumn();

// Role-Specific Stats
$stuBalance = 0;
$pendingReqs = 0;
$totalPendingReqs = 0;
$totalUnpaidFees = 0;
$totalPendingPayments = 0;
$totalDraftVouchers = 0;

if ($_SESSION['role'] === 'student') {
    $stuBalance = $pdo->prepare("SELECT SUM(balance_due) FROM invoices WHERE user_id = ?");
    $stuBalance->execute([$_SESSION['user_id']]);
    $stuBalance = $stuBalance->fetchColumn() ?: 0;
    
    $pendingReqCount = $pdo->prepare("SELECT COUNT(*) FROM student_requests WHERE user_id = ? AND status = 'pending'");
    $pendingReqCount->execute([$_SESSION['user_id']]);
    $pendingReqs = $pendingReqCount->fetchColumn();
} else if (in_array($_SESSION['role'], ['finance', 'super_admin', 'clerk'])) {
    $totalPendingReqs = $pdo->query("SELECT COUNT(*) FROM student_requests WHERE status = 'pending'")->fetchColumn();
    $totalUnpaidFees = $pdo->query("SELECT SUM(balance_due) FROM invoices WHERE status != 'paid'")->fetchColumn() ?: 0;
    $totalPendingPayments = $pdo->query("SELECT COUNT(*) FROM payments WHERE verification_status = 'pending'")->fetchColumn();
    $totalDraftVouchers = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'draft'")->fetchColumn();
}
?>

<style>
    .hero-card { background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%); border: none; border-radius: 24px; color: white; position: relative; overflow: hidden; }
    .hero-card::after { content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px; background: rgba(255,255,255,0.1); border-radius: 50%; }
    .stat-card-modern { background: var(--glass-bg); backdrop-filter: blur(10px); border: 1px solid var(--glass-border); border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.05); transition: all 0.3s; height: 100%; color: var(--glass-text); }
    .stat-card-modern:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
    .icon-circle { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .quick-action-btn { border-radius: 15px; padding: 15px; text-align: left; transition: all 0.2s; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--glass-text); width: 100%; display: flex; align-items: center; }
    .quick-action-btn:hover { background: rgba(102, 16, 242, 0.1); border-color: #6610f2; color: #6610f2; }
    .progress-custom { height: 8px; border-radius: 10px; background: rgba(0,0,0,0.1); }
    [data-bs-theme="dark"] .progress-custom { background: rgba(255,255,255,0.1); }
</style>

<!-- Welcome Header -->
<div class="card hero-card shadow-lg p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h2>
            <p class="mb-0 opacity-75">You are logged in as <span class="badge bg-white text-primary rounded-pill"><?= ucfirst($_SESSION['role']) ?></span>. Here's what's happening today.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-inline-block text-start bg-white bg-opacity-25 p-3 rounded-4">
                <div class="small opacity-75">System Integrity</div>
                <div class="fw-bold"><i class="bi bi-shield-check me-1"></i> Highly Secure</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Role Based Stats -->
    <?php if($_SESSION['role'] === 'student'): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card-modern p-4">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-circle bg-danger-subtle text-danger"><i class="bi bi-wallet2"></i></div>
                    <span class="badge bg-danger text-white rounded-pill align-self-start">Outstanding</span>
                </div>
                <h6 class="text-muted small fw-bold text-uppercase">Total Balance Due</h6>
                <h2 class="fw-bold mb-0">PKR <?= number_format($stuBalance, 0) ?></h2>
                <a href="dashboards/student/my_fees.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card-modern p-4">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-circle bg-warning-subtle text-warning"><i class="bi bi-chat-left-dots"></i></div>
                    <span class="badge bg-warning text-dark rounded-pill align-self-start">Pending</span>
                </div>
                <h6 class="text-muted small fw-bold text-uppercase">Support Requests</h6>
                <h2 class="fw-bold mb-0"><?= $pendingReqs ?></h2>
                <a href="dashboards/student/my_fees.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-12 col-xl-4">
            <?php 
                $paidAmt = $pdo->prepare("SELECT SUM(total_base_amount - balance_due) FROM invoices WHERE user_id = ?");
                $paidAmt->execute([$_SESSION['user_id']]);
                $paid = $paidAmt->fetchColumn() ?: 0;
                $totalAmt = $pdo->prepare("SELECT SUM(total_base_amount) FROM invoices WHERE user_id = ?");
                $totalAmt->execute([$_SESSION['user_id']]);
                $total = max(1, $totalAmt->fetchColumn() ?: 1);
                $perc = round(($paid / $total) * 100);
            ?>
            <div class="card stat-card-modern p-4 bg-primary text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="d-flex justify-content-between mb-3">
                    <div class="icon-circle bg-white bg-opacity-25 text-white"><i class="bi bi-graph-up-arrow"></i></div>
                    <span class="text-white opacity-75 fw-bold"><?= $perc ?>%</span>
                </div>
                <h6 class="text-white text-opacity-75 small fw-bold text-uppercase">Fee Clearance</h6>
                <div class="progress progress-custom bg-white bg-opacity-25 mb-3">
                    <div class="progress-bar bg-white" style="width: <?= $perc ?>%"></div>
                </div>
                <p class="small mb-0">You've cleared more than half your dues!</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if(in_array($_SESSION['role'], ['finance', 'clerk', 'super_admin'])): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-success-subtle text-success me-3"><i class="bi bi-people"></i></div>
                    <div>
                        <h6 class="text-muted x-small fw-bold text-uppercase mb-0">Global Requests</h6>
                        <h3 class="fw-bold mb-0"><?= $totalPendingReqs ?></h3>
                    </div>
                </div>
                <a href="dashboards/finance/manage_requests.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-warning-subtle text-warning me-3"><i class="bi bi-shield-check"></i></div>
                    <div>
                        <h6 class="text-muted x-small fw-bold text-uppercase mb-0">Pending Verify</h6>
                        <h3 class="fw-bold mb-0"><?= $totalPendingPayments ?></h3>
                    </div>
                </div>
                <a href="dashboards/clerk/verify_payments.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-info-subtle text-info me-3"><i class="bi bi-mailbox"></i></div>
                    <div>
                        <h6 class="text-muted x-small fw-bold text-uppercase mb-0">Draft Vouchers</h6>
                        <h3 class="fw-bold mb-0"><?= $totalDraftVouchers ?></h3>
                    </div>
                </div>
                <a href="dashboards/clerk/vouchers.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-primary-subtle text-primary me-3"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <h6 class="text-muted x-small fw-bold text-uppercase mb-0">Outstanding</h6>
                        <h3 class="fw-bold mb-0"><?= number_format($totalUnpaidFees/1000, 1) ?>K</h3>
                    </div>
                </div>
                <a href="dashboards/finance/reports.php" class="stretched-link"></a>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row g-4">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <h5 class="fw-bold mb-3">Quick Navigation</h5>
        <div class="row g-2">
            <?php if($_SESSION['role'] === 'student'): ?>
                <div class="col-12"><a href="dashboards/student/my_fees.php" class="quick-action-btn"><i class="bi bi-receipt me-3 fs-5"></i> My Invoices & Payments</a></div>
                <div class="col-12"><a href="dashboards/student/pay_fee.php" class="quick-action-btn"><i class="bi bi-credit-card me-3 fs-5"></i> Pay Fee Online</a></div>
            <?php endif; ?>
            
            <?php if(in_array($_SESSION['role'], ['finance', 'super_admin'])): ?>
                <div class="col-12"><a href="dashboards/finance/manage_fees.php" class="quick-action-btn"><i class="bi bi-gear me-3 fs-5"></i> Setup Fee Structure</a></div>
                <div class="col-12"><a href="dashboards/finance/manage_scholarships.php" class="quick-action-btn"><i class="bi bi-award me-3 fs-5"></i> Award Scholarships</a></div>
                <div class="col-12"><a href="dashboards/finance/reports.php" class="quick-action-btn"><i class="bi bi-pie-chart me-3 fs-5"></i> Financial Reports</a></div>
            <?php endif; ?>

            <?php if($_SESSION['role'] === 'clerk'): ?>
                <div class="col-12"><a href="dashboards/clerk/vouchers.php" class="quick-action-btn"><i class="bi bi-mailbox me-3 fs-5"></i> Dispatch Vouchers</a></div>
                <div class="col-12"><a href="dashboards/clerk/verify_payments.php" class="quick-action-btn"><i class="bi bi-shield-check me-3 fs-5"></i> Verify Payments</a></div>
            <?php endif; ?>

            <?php if($_SESSION['role'] === 'super_admin'): ?>
                <div class="col-12"><a href="dashboards/super_admin/manage_users.php" class="quick-action-btn"><i class="bi bi-people me-3 fs-5"></i> Manage Users</a></div>
                <div class="col-12"><a href="dashboards/super_admin/manage_departments.php" class="quick-action-btn"><i class="bi bi-building me-3 fs-5"></i> Academic Setup</a></div>
            <?php endif; ?>
            <div class="col-12"><a href="profile.php" class="quick-action-btn"><i class="bi bi-person-circle me-3 fs-5"></i> My Profile</a></div>
        </div>
    </div>

    <!-- Recent Activity / News -->
    <div class="col-lg-8">
        <div class="card stat-card-modern p-4">
            <h5 class="fw-bold mb-4">Latest System Overview</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light opacity-50">
                        <tr>
                            <th class="border-0 small text-uppercase">Activity</th>
                            <th class="border-0 small text-uppercase">Time</th>
                            <th class="border-0 small text-uppercase text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch a mix of recent activities
                        $activities = $pdo->query("
                            (SELECT 'New Request' as type, created_at as time, status FROM student_requests ORDER BY created_at DESC LIMIT 3)
                            UNION
                            (SELECT 'Payment' as type, paid_date as time, verification_status as status FROM payments ORDER BY paid_date DESC LIMIT 3)
                            ORDER BY time DESC LIMIT 5
                        ")->fetchAll();
                        
                        foreach($activities as $act):
                            $bg = ($act['type'] == 'Payment') ? 'bg-success' : 'bg-info';
                            $icon = ($act['type'] == 'Payment') ? 'bi-cash' : 'bi-chat-left';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-opacity-10 <?= $bg ?> text-white me-3" style="width: 35px; height: 35px; min-width:35px; font-size: 1rem; background-color: unset !important; color: unset !important; border: 1px solid #eee;">
                                        <i class="bi <?= $icon ?> text-primary"></i>
                                    </div>
                                    <div class="fw-bold small"><?= $act['type'] ?> from Student</div>
                                </div>
                            </td>
                            <td class="text-muted small"><?= date('M d, H:i', strtotime($act['time'])) ?></td>
                            <td class="text-end"><span class="badge bg-light text-dark border rounded-pill"><?= ucfirst($act['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($activities)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No recent activity detected.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
</style>

<?php require_once 'includes/footer.php'; ?>