<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

include('includes/header.php'); 

// Fetch Stats
$s1 = oci_parse($dbconn, "SELECT COUNT(*) AS TOTAL FROM STAFFS"); oci_execute($s1); $r1 = oci_fetch_array($s1, OCI_ASSOC);
$s2 = oci_parse($dbconn, "SELECT COUNT(*) AS TOTAL FROM FRUITS"); oci_execute($s2); $r2 = oci_fetch_array($s2, OCI_ASSOC);
$s3 = oci_parse($dbconn, "SELECT SUM(TotalAmount) AS TOTAL FROM ORDERS WHERE TRUNC(OrderDate) = TRUNC(SYSDATE)"); oci_execute($s3); $r3 = oci_fetch_array($s3, OCI_ASSOC);

// Chart Data (Pie)
$sql_pie = "SELECT Category, COUNT(*) as TOTAL FROM FRUITS GROUP BY Category";
$stmt_pie = oci_parse($dbconn, $sql_pie); oci_execute($stmt_pie);
$kategori_label = []; $kategori_data = [];
while($row = oci_fetch_array($stmt_pie, OCI_ASSOC)){ $kategori_label[] = $row['CATEGORY']; $kategori_data[] = $row['TOTAL']; }

// Chart Data (Bar)
$sql_bar = "SELECT FruitName, QuantityStock FROM FRUITS ORDER BY QuantityStock DESC FETCH FIRST 5 ROWS ONLY";
$stmt_bar = oci_parse($dbconn, $sql_bar); oci_execute($stmt_bar);
$buah_label = []; $buah_stok = [];
while($row = oci_fetch_array($stmt_bar, OCI_ASSOC)){ $buah_label[] = $row['FRUITNAME']; $buah_stok[] = $row['QUANTITYSTOCK']; }
?>

<style>
    .stat-card {
        transition: all 0.3s ease;
        border: none;
        overflow: hidden;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2) !important;
    }
    .stat-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 5rem;
        opacity: 0.15;
        transform: rotate(-15deg);
    }
    .chart-container {
        padding: 15px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold text-shadow">
            <i class="fas fa-chart-line me-2"></i>Admin Overview
        </h2>
        <span class="badge bg-light text-dark p-2 shadow-sm">
            <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y'); ?>
        </span>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="glass-card stat-card border-start border-primary border-5">
                <div class="p-3">
                    <h6 class="text-uppercase fw-bold text-muted small">Total Employees</h6>
                    <h2 class="display-5 fw-bold text-primary mb-0"><?php echo $r1['TOTAL']; ?></h2>
                    <p class="mb-0 small text-muted">Active staff members</p>
                </div>
                <i class="fas fa-users stat-icon text-primary"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card stat-card border-start border-success border-5">
                <div class="p-3">
                    <h6 class="text-uppercase fw-bold text-muted small">Fruit Inventory</h6>
                    <h2 class="display-5 fw-bold text-success mb-0"><?php echo $r2['TOTAL']; ?></h2>
                    <p class="mb-0 small text-muted">Varieties in stock</p>
                </div>
                <i class="fas fa-apple-alt stat-icon text-success"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card stat-card border-start border-warning border-5">
                <div class="p-3">
                    <h6 class="text-uppercase fw-bold text-muted small">Today's Revenue</h6>
                    <h2 class="display-5 fw-bold text-warning mb-0">RM <?php echo number_format($r3['TOTAL'] ?? 0, 2); ?></h2>
                    <p class="mb-0 small text-muted">Sales for today</p>
                </div>
                <i class="fas fa-wallet stat-icon text-warning"></i>
            </div>
        </div>
    </div>

    <div class="row mt-4 g-4">
        <div class="col-md-5">
            <div class="glass-card chart-container">
                <h5 class="fw-bold mb-4"><i class="fas fa-chart-pie me-2 text-info"></i>Inventory by Category</h5>
                <div style="height: 300px;">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="glass-card chart-container">
                <h5 class="fw-bold mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Top 5 Stock Levels</h5>
                <div style="height: 300px;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: { 
            labels: <?php echo json_encode($kategori_label); ?>, 
            datasets: [{ 
                data: <?php echo json_encode($kategori_data); ?>, 
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                hoverOffset: 10,
                borderWidth: 2,
                borderColor: '#ffffff'
            }] 
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: { 
            labels: <?php echo json_encode($buah_label); ?>, 
            datasets: [{ 
                label: 'Stock Count', 
                data: <?php echo json_encode($buah_stok); ?>, 
                backgroundColor: 'rgba(78, 115, 223, 0.8)', 
                borderColor: '#4e73df', 
                borderWidth: 1, 
                borderRadius: 10,
                barThickness: 40
            }] 
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            scales: { 
                y: { beginAtZero: true, grid: { display: false } },
                x: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
</script>
</body>
</html>