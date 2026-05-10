<?php 
require_once '../../includes/header.php'; 

// 1. Core Summary Statistics
$total_collected = $pdo->query("SELECT SUM(amount) FROM payments")->fetchColumn() ?: 0;
$total_target = $pdo->query("SELECT SUM(payable_amount) FROM invoices")->fetchColumn() ?: 0;
$total_pending = $total_target - $total_collected;
$collection_rate = $total_target > 0 ? ($total_collected / $total_target) * 100 : 0;

$def_count = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM invoices WHERE balance_due > 0")->fetchColumn() ?: 0;

// 1.5 System Counts for Overview
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn() ?: 0;
$total_vouchers = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() ?: 0;
$total_scholarships = $pdo->query("SELECT COUNT(*) FROM user_scholarships")->fetchColumn() ?: 0;
$total_installments = $pdo->query("SELECT COUNT(DISTINCT invoice_id) FROM installments")->fetchColumn() ?: 0;


// 2. Program-wise Revenue Data for Chart
$program_data = $pdo->query("
    SELECT p.name, SUM(inv.payable_amount) as total_target, SUM(inv.payable_amount - inv.balance_due) as collected 
    FROM programs p 
    JOIN semesters s ON p.id = s.program_id 
    JOIN invoices inv ON s.id = inv.semester_id 
    GROUP BY p.id
")->fetchAll();

$prog_labels = []; $prog_collected = []; $prog_pending = [];
foreach($program_data as $pd) {
    $prog_labels[] = $pd['name'];
    $prog_collected[] = (float)$pd['collected'];
    $prog_pending[] = (float)$pd['total_target'] - $pd['collected'];
}

// 3. Category-wise Breakdown
$cat_data = $pdo->query("
    SELECT u.fee_category, SUM(i.payable_amount) as total 
    FROM invoices i 
    JOIN users u ON i.user_id = u.id 
    GROUP BY u.fee_category
")->fetchAll();
$cat_labels = []; $cat_values = [];
foreach($cat_data as $cd) {
    $cat_labels[] = $cd['fee_category'] ?: 'Regular';
    $cat_values[] = (float)$cd['total'];
}
?>

<style>
    .stat-card { border: none; border-radius: 16px; overflow: hidden; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-5px); }
    .progress-ring { height: 8px; border-radius: 4px; background: rgba(0,0,0,0.05); }
    .gradient-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
    .gradient-info { background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); }
    .gradient-warning { background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%); }
    .gradient-danger { background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%); }
</style>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card gradient-success text-white h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3 text-white">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <span class="badge bg-white text-success px-2 py-1">PKR</span>
                </div>
                <h6 class="text-white text-opacity-75 small text-uppercase fw-bold">Total Collected</h6>
                <h3 class="fw-bold mb-2">M <?= number_format($total_collected / 1000000, 2) ?></h3>
                <div class="progress progress-ring mb-1">
                    <div class="progress-bar bg-white" role="progressbar" style="width: <?= $collection_rate ?>%"></div>
                </div>
                <small class="text-white text-opacity-75"><?= number_format($collection_rate, 1) ?>% of Target Achievement</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card gradient-danger text-white h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3 text-white">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
                <h6 class="text-white text-opacity-75 small text-uppercase fw-bold">Outstanding Dues</h6>
                <h3 class="fw-bold mb-2">K <?= number_format($total_pending / 1000, 1) ?></h3>
                <p class="mb-0 small"><i class="bi bi-graph-down me-1"></i> From <?= $def_count ?> Students</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card gradient-info text-white h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3 text-white">
                        <i class="bi bi-trophy fs-4"></i>
                    </div>
                </div>
                <h6 class="text-white text-opacity-75 small text-uppercase fw-bold">Scholarships Disbursed</h6>
                <?php $total_disc = $pdo->query("SELECT SUM(discount_amount) FROM invoices")->fetchColumn() ?: 0; ?>
                <h3 class="fw-bold mb-2">K <?= number_format($total_disc / 1000, 1) ?></h3>
                <p class="mb-0 small">Support for local talent</p>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card gradient-warning text-white h-100 shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-shape bg-white bg-opacity-25 rounded-circle p-3 text-white">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                </div>
                <h6 class="text-white text-opacity-75 small text-uppercase fw-bold">Current Target</h6>
                <h3 class="fw-bold mb-2">M <?= number_format($total_target / 1000000, 2) ?></h3>
                <p class="mb-0 small">Based on active semester billing</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Row 2: System Overviews (Counts) -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white h-100 shadow-sm" style="border-radius: 16px; border-left: 5px solid #0d6efd;">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Registered Students</h6>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_students) ?> <i class="bi bi-people text-primary float-end opacity-50"></i></h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white h-100 shadow-sm" style="border-radius: 16px; border-left: 5px solid #6f42c1;">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Generated Vouchers</h6>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_vouchers) ?> <i class="bi bi-receipt text-purple float-end opacity-50" style="color: #6f42c1;"></i></h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white h-100 shadow-sm" style="border-radius: 16px; border-left: 5px solid #fd7e14;">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Active Installments</h6>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_installments) ?> <i class="bi bi-calendar-range text-warning float-end opacity-50" style="color: #fd7e14;"></i></h3>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-white h-100 shadow-sm" style="border-radius: 16px; border-left: 5px solid #198754;">
            <div class="card-body">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Scholarships Awarded</h6>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($total_scholarships) ?> <i class="bi bi-award text-success float-end opacity-50"></i></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="card-title fw-bold text-dark"><i class="bi bi-bar-chart-fill me-2 text-primary"></i> Collection Status by Program</h5>
            </div>
            <div class="card-body">
                <canvas id="programChart" style="min-height: 350px; height: 350px; max-height: 350px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="card-title fw-bold text-dark"><i class="bi bi-pie-chart-fill me-2 text-info"></i> Revenue by Category</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                <canvas id="categoryChart" style="min-height: 250px; max-height: 250px;"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Stacked Bar Chart for Programs
new Chart(document.getElementById('programChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($prog_labels) ?>,
        datasets: [
            {
                label: 'Collected',
                data: <?= json_encode($prog_collected) ?>,
                backgroundColor: '#28a745',
                borderRadius: 4
            },
            {
                label: 'Remaining',
                data: <?= json_encode($prog_pending) ?>,
                backgroundColor: '#dc3545',
                borderRadius: 4
            }
        ]
    },
    options: { 
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { x: { stacked: true }, y: { stacked: true } }
    }
});

// Pie Chart for Categories
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($cat_labels) ?>,
        datasets: [{
            data: <?= json_encode($cat_values) ?>,
            backgroundColor: ['#0d6efd', '#6610f2', '#6f42c1', '#fd7e14'],
            borderWidth: 0
        }]
    },
    options: { 
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
