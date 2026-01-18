<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { 
    header("Location: login.php"); exit(); 
}
include('includes/header.php');

// 1. KIRA STATISTIK WIDGET
$s1 = oci_parse($dbconn, "SELECT COUNT(*) AS CNT FROM STAFFS");
oci_execute($s1);
$staff_cnt = oci_fetch_assoc($s1)['CNT'];

$s2 = oci_parse($dbconn, "SELECT COUNT(*) AS CNT FROM FRUITS");
oci_execute($s2);
$fruit_cnt = oci_fetch_assoc($s2)['CNT'];

$s3 = oci_parse($dbconn, "SELECT NVL(SUM(TotalAmount), 0) AS TOTAL FROM ORDERS WHERE TRUNC(OrderDate) = TRUNC(SYSDATE)");
oci_execute($s3);
$today_sales = oci_fetch_assoc($s3)['TOTAL'];

// 2. DATA PIE CHART
$cat_labels = []; $cat_data = [];
$q_cat = oci_parse($dbconn, "SELECT Category, COUNT(*) as Cnt FROM FRUITS GROUP BY Category");
oci_execute($q_cat);
while ($r = oci_fetch_assoc($q_cat)) {
    $cat_labels[] = $r['CATEGORY'];
    $cat_data[] = $r['CNT'];
}

// 3. DATA BAR CHART (Top 5 Stock)
$stk_labels = []; $stk_data = [];
$q_stk = oci_parse($dbconn, "SELECT * FROM (SELECT FruitName, QuantityStock FROM FRUITS ORDER BY QuantityStock DESC) WHERE ROWNUM <= 5");
oci_execute($q_stk);
while ($r = oci_fetch_assoc($q_stk)) {
    $stk_labels[] = $r['FRUITNAME'];
    $stk_data[] = $r['QUANTITYSTOCK'];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-white text-shadow mb-0"><i class="fas fa-chart-line me-2"></i>Admin Dashboard</h3>
            <span class="badge bg-light text-dark shadow-sm p-2 mt-1"><i class="fas fa-user-shield me-2"></i>Logged in as Administrator</span>
        </div>
        
        <a href="sales_form.php" class="btn btn-warning fw-bold shadow-lg px-4 py-2 rounded-pill border-2 border-white">
            <i class="fas fa-cash-register me-2"></i> New Sales
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="position-absolute end-0 bottom-0 p-3 opacity-25"><i class="fas fa-users fa-4x text-primary"></i></div>
                <small class="fw-bold text-muted text-uppercase">Total Employees</small>
                <h1 class="display-4 fw-bold text-primary mb-0"><?php echo $staff_cnt; ?></h1>
                <small>Active staff members</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="position-absolute end-0 bottom-0 p-3 opacity-25"><i class="fas fa-apple-alt fa-4x text-success"></i></div>
                <small class="fw-bold text-muted text-uppercase">Fruit Inventory</small>
                <h1 class="display-4 fw-bold text-success mb-0"><?php echo $fruit_cnt; ?></h1>
                <small>Varieties in stock</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="position-absolute end-0 bottom-0 p-3 opacity-25"><i class="fas fa-wallet fa-4x text-warning"></i></div>
                <small class="fw-bold text-muted text-uppercase">Today's Revenue</small>
                <h1 class="display-4 fw-bold text-warning mb-0">RM <?php echo number_format($today_sales, 2); ?></h1>
                <small>Sales for today</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="glass-card p-4 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2 text-info"></i>Inventory by Category</h6>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="catChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card p-4 h-100">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>Top 5 Stock Levels</h6>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const catLabels = <?php echo json_encode($cat_labels); ?>;
const catData = <?php echo json_encode($cat_data); ?>;
const stkLabels = <?php echo json_encode($stk_labels); ?>;
const stkData = <?php echo json_encode($stk_data); ?>;

// 1. Pie Chart
new Chart(document.getElementById('catChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{
            data: catData,
            backgroundColor: ['#36a2eb', '#4bc0c0', '#ffcd56', '#ff6384'],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: { 
        maintainAspectRatio: false, 
        plugins: { legend: { position: 'bottom' } }
    }
});

// 2. Bar Chart
new Chart(document.getElementById('stockChart'), {
    type: 'bar',
    data: {
        labels: stkLabels,
        datasets: [{
            label: 'Stock Units',
            data: stkData,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 5,
            barThickness: 40
        }]
    },
    options: {
        maintainAspectRatio: false,
        scales: { 
            y: { 
                beginAtZero: true, 
                grid: { display: true, color: "rgba(0,0,0,0.05)" }
            },
            x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } }
    }
});
</script>
</body>
</html>