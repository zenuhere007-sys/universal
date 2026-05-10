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
    /* Hero Banner */
    .hero-card { 
        background: linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%); 
        border: none; 
        border-radius: 24px; 
        color: white; 
        position: relative; 
        overflow: hidden; 
        box-shadow: 0 15px 35px rgba(58, 28, 113, 0.2);
    }
    .hero-card::before {
        content: ''; position: absolute; top: -50%; left: -10%; width: 50%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        transform: rotate(30deg);
    }
    .hero-card::after { 
        content: ''; position: absolute; bottom: -20%; right: -5%; width: 300px; height: 300px; 
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); 
        border-radius: 50%; 
    }
    
    /* Stat Cards */
    .stat-card-modern { 
        background: var(--glass-bg); 
        backdrop-filter: blur(10px); 
        border: 1px solid var(--glass-border); 
        border-radius: 20px; 
        box-shadow: 0 8px 24px rgba(0,0,0,0.04); 
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        height: 100%; 
        color: var(--glass-text); 
        position: relative;
        overflow: hidden;
    }
    .stat-card-modern:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 18px 40px rgba(0,0,0,0.08); 
    }
    .stat-card-modern::before {
        content: ''; position: absolute; top: 0; left: 0; width: 5px; height: 100%;
    }
    .stat-req::before { background: #198754; }
    .stat-ver::before { background: #fd7e14; }
    .stat-vou::before { background: #0dcaf0; }
    .stat-out::before { background: #dc3545; }

    .icon-circle { 
        width: 55px; height: 55px; 
        border-radius: 16px; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 1.6rem; 
        transition: transform 0.3s;
    }
    .stat-card-modern:hover .icon-circle { transform: scale(1.1) rotate(5deg); }

    /* Quick Actions Grid */
    .quick-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }
    .quick-action-btn { 
        border-radius: 16px; 
        padding: 16px; 
        text-align: left; 
        transition: all 0.3s ease; 
        border: 1px solid var(--glass-border); 
        background: var(--glass-bg); 
        color: var(--glass-text); 
        display: flex; 
        align-items: center;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.02);
    }
    .quick-action-btn:hover { 
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-color: #adb5bd; 
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    [data-bs-theme="dark"] .quick-action-btn:hover {
        background: linear-gradient(135deg, #2b3035 0%, #343a40 100%);
        border-color: #495057;
    }
    .quick-action-icon {
        width: 40px; height: 40px;
        border-radius: 12px;
        background: rgba(102, 16, 242, 0.1);
        color: #6610f2;
        display: flex; align-items: center; justify-content: center;
        margin-right: 15px;
        font-size: 1.2rem;
    }
    [data-bs-theme="dark"] .quick-action-icon { background: rgba(177, 133, 255, 0.2); color: #b185ff; }

    /* Table Styling */
    .table-modern { border-collapse: separate; border-spacing: 0 8px; }
    .table-modern thead th { border: none; padding-bottom: 0; color: #adb5bd; font-size: 0.75rem; }
    .table-modern tbody tr { box-shadow: 0 2px 8px rgba(0,0,0,0.02); border-radius: 12px; transition: transform 0.2s; }
    .table-modern tbody tr:hover { transform: scale(1.01); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .table-modern tbody td { background: var(--glass-bg); border: none; padding: 15px 20px; }
    .table-modern tbody td:first-child { border-radius: 12px 0 0 12px; }
    .table-modern tbody td:last-child { border-radius: 0 12px 12px 0; }
</style>

<!-- Welcome Header -->
<div class="card hero-card p-5 mb-5">
    <div class="row align-items-center position-relative" style="z-index: 1;">
        <div class="col-md-8 text-center text-md-start">
            <h1 class="fw-bolder mb-2 display-5" style="letter-spacing: -1px;">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?>!</h1>
            <p class="mb-0 fs-5 opacity-75">You are logged in as <span class="badge bg-white text-dark rounded-pill shadow-sm px-3 py-2 ms-1 fw-bold"><?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?></span></p>
        </div>
        <div class="col-md-4 text-center text-md-end mt-4 mt-md-0">
            <div class="d-inline-block text-start bg-white bg-opacity-25 p-3 rounded-4 shadow-sm backdrop-blur">
                <div class="small opacity-75 text-uppercase fw-bold mb-1" style="letter-spacing: 1px;">System Integrity</div>
                <div class="fs-5 fw-bold"><i class="bi bi-shield-fill-check text-white me-2"></i>Highly Secure</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Role Based Stats -->
    <?php if($_SESSION['role'] === 'student'): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card-modern stat-out p-4">
                <div class="d-flex justify-content-between mb-4">
                    <div class="icon-circle bg-danger-subtle text-danger shadow-sm"><i class="bi bi-wallet2"></i></div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill align-self-start px-3 py-2">Outstanding</span>
                </div>
                <h6 class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">Total Balance Due</h6>
                <h2 class="fw-bolder mb-0">PKR <?= number_format($stuBalance, 0) ?></h2>
                <a href="dashboards/student/my_fees.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card-modern stat-ver p-4">
                <div class="d-flex justify-content-between mb-4">
                    <div class="icon-circle bg-warning-subtle text-warning shadow-sm"><i class="bi bi-chat-left-dots"></i></div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill align-self-start px-3 py-2">Pending</span>
                </div>
                <h6 class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">Support Requests</h6>
                <h2 class="fw-bolder mb-0"><?= $pendingReqs ?></h2>
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
            <div class="card stat-card-modern p-4 text-white shadow" style="background: linear-gradient(135deg, #00C9FF 0%, #92FE9D 100%); border: none;">
                <div class="d-flex justify-content-between mb-4">
                    <div class="icon-circle bg-white bg-opacity-25 text-white shadow-sm"><i class="bi bi-graph-up-arrow"></i></div>
                    <span class="text-white opacity-75 fw-bold fs-5"><?= $perc ?>%</span>
                </div>
                <h6 class="text-white text-opacity-75 small fw-bold text-uppercase mb-2" style="letter-spacing: 1px;">Fee Clearance</h6>
                <div class="progress mb-3" style="height: 10px; background: rgba(0,0,0,0.1); border-radius: 10px;">
                    <div class="progress-bar bg-white" style="width: <?= $perc ?>%; border-radius: 10px;"></div>
                </div>
                <p class="small mb-0 text-dark fw-bold opacity-75">You're making great progress!</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if(in_array($_SESSION['role'], ['finance', 'clerk', 'super_admin'])): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern stat-req p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-success-subtle text-success me-3 shadow-sm"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Global Requests</h6>
                        <h2 class="fw-bolder mb-0"><?= $totalPendingReqs ?></h2>
                    </div>
                </div>
                <a href="dashboards/finance/manage_requests.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern stat-ver p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-warning-subtle text-warning me-3 shadow-sm" style="color: #fd7e14 !important;"><i class="bi bi-shield-fill-check"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Pending Verify</h6>
                        <h2 class="fw-bolder mb-0"><?= $totalPendingPayments ?></h2>
                    </div>
                </div>
                <a href="dashboards/clerk/verify_payments.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern stat-vou p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-info-subtle text-info me-3 shadow-sm"><i class="bi bi-mailbox2"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Draft Vouchers</h6>
                        <h2 class="fw-bolder mb-0"><?= $totalDraftVouchers ?></h2>
                    </div>
                </div>
                <a href="dashboards/clerk/vouchers.php" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card stat-card-modern stat-out p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle bg-danger-subtle text-danger me-3 shadow-sm"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Outstanding</h6>
                        <h2 class="fw-bolder mb-0"><?= number_format($totalUnpaidFees/1000, 1) ?>K</h2>
                    </div>
                </div>
                <a href="dashboards/finance/reports.php" class="stretched-link"></a>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row g-5">
    <!-- Quick Actions -->
    <div class="col-lg-5">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-primary rounded-circle me-3" style="width: 12px; height: 12px;"></div>
            <h4 class="fw-bold mb-0">Quick Navigation</h4>
        </div>
        <div class="quick-action-grid">
            <?php if($_SESSION['role'] === 'student'): ?>
                <a href="dashboards/student/my_fees.php" class="quick-action-btn">
                    <div class="quick-action-icon"><i class="bi bi-receipt"></i></div>
                    <span class="fw-semibold">My Invoices & Payments</span>
                </a>
                <a href="dashboards/student/pay_fee.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-success-subtle text-success"><i class="bi bi-credit-card"></i></div>
                    <span class="fw-semibold">Pay Fee Online</span>
                </a>
            <?php endif; ?>
            
            <?php if(in_array($_SESSION['role'], ['finance', 'super_admin'])): ?>
                <a href="dashboards/finance/manage_fees.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-info-subtle text-info"><i class="bi bi-gear-fill"></i></div>
                    <span class="fw-semibold">Setup Fee Structure</span>
                </a>
                <a href="dashboards/finance/manage_scholarships.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-warning-subtle text-warning"><i class="bi bi-award-fill"></i></div>
                    <span class="fw-semibold">Award Scholarships</span>
                </a>
                <a href="dashboards/finance/reports.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-danger-subtle text-danger"><i class="bi bi-pie-chart-fill"></i></div>
                    <span class="fw-semibold">Financial Reports</span>
                </a>
            <?php endif; ?>

            <?php if($_SESSION['role'] === 'clerk'): ?>
                <a href="dashboards/clerk/vouchers.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-primary-subtle text-primary"><i class="bi bi-send-fill"></i></div>
                    <span class="fw-semibold">Dispatch Vouchers</span>
                </a>
                <a href="dashboards/clerk/verify_payments.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-success-subtle text-success"><i class="bi bi-shield-fill-check"></i></div>
                    <span class="fw-semibold">Verify Payments</span>
                </a>
            <?php endif; ?>

            <?php if($_SESSION['role'] === 'super_admin'): ?>
                <a href="dashboards/super_admin/manage_users.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-purple-subtle text-purple" style="color: #6f42c1; background: #e0cffc;"><i class="bi bi-people-fill"></i></div>
                    <span class="fw-semibold">Manage Users</span>
                </a>
                <a href="dashboards/super_admin/manage_departments.php" class="quick-action-btn">
                    <div class="quick-action-icon bg-secondary-subtle text-secondary"><i class="bi bi-building-fill-add"></i></div>
                    <span class="fw-semibold">Academic Setup</span>
                </a>
            <?php endif; ?>
            
            <a href="profile.php" class="quick-action-btn">
                <div class="quick-action-icon bg-dark-subtle text-dark"><i class="bi bi-person-circle"></i></div>
                <span class="fw-semibold">My Profile</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity / News -->
    <div class="col-lg-7">
        <div class="d-flex align-items-center mb-4">
            <div class="bg-success rounded-circle me-3" style="width: 12px; height: 12px;"></div>
            <h4 class="fw-bold mb-0">System Activity Log</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-modern w-100">
                <thead class="text-uppercase" style="letter-spacing: 1px;">
                    <tr>
                        <th class="ps-4">Activity Event</th>
                        <th>Timestamp</th>
                        <th class="text-end pe-4">Current Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch a mix of recent activities
                    $activities = $pdo->query("
                        (SELECT 'Student Fee Request' as type, created_at as time, status FROM student_requests ORDER BY created_at DESC LIMIT 3)
                        UNION
                        (SELECT 'Fee Payment Submission' as type, paid_date as time, verification_status as status FROM payments ORDER BY paid_date DESC LIMIT 3)
                        ORDER BY time DESC LIMIT 5
                    ")->fetchAll();
                    
                    foreach($activities as $act):
                        $bg = ($act['type'] == 'Fee Payment Submission') ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary';
                        $icon = ($act['type'] == 'Fee Payment Submission') ? 'bi-cash-coin' : 'bi-chat-right-text-fill';
                        $statusColor = 'bg-light text-dark';
                        if ($act['status'] == 'approved' || $act['status'] == 'verified') $statusColor = 'bg-success text-white';
                        if ($act['status'] == 'pending') $statusColor = 'bg-warning text-dark';
                        if ($act['status'] == 'rejected') $statusColor = 'bg-danger text-white';
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle <?= $bg ?> me-3 shadow-sm" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div class="fw-bold"><?= $act['type'] ?></div>
                            </div>
                        </td>
                        <td class="text-muted align-middle">
                            <i class="bi bi-clock me-1 opacity-50"></i> <?= date('M d, g:i A', strtotime($act['time'])) ?>
                        </td>
                        <td class="text-end pe-4 align-middle">
                            <span class="badge <?= $statusColor ?> rounded-pill px-3 py-2 shadow-sm" style="letter-spacing: 0.5px;"><?= ucfirst($act['status']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($activities)): ?>
                        <tr><td colspan="3" class="text-center py-5 text-muted bg-white rounded-4 shadow-sm"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i> No recent activity detected.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>