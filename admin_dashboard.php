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

// Chart Data
$sql_pie = "SELECT Category, COUNT(*) as TOTAL FROM FRUITS GROUP BY Category";
$stmt_pie = oci_parse($dbconn, $sql_pie); oci_execute($stmt_pie);
$kategori_label = []; $kategori_data = [];
while($row = oci_fetch_array($stmt_pie, OCI_ASSOC)){ $kategori_label[] = $row['CATEGORY']; $kategori_data[] = $row['TOTAL']; }

$sql_bar = "SELECT FruitName, QuantityStock FROM FRUITS ORDER BY QuantityStock DESC FETCH FIRST 5 ROWS ONLY";
$stmt_bar = oci_parse($dbconn, $sql_bar); oci_execute($stmt_bar);
$buah_label = []; $buah_stok = [];
while($row = oci_fetch_array($stmt_bar, OCI_ASSOC)){ $buah_label[] = $row['FRUITNAME']; $buah_stok[] = $row['QUANTITYSTOCK']; }
?>

<div class="container-fluid">
    <h2 class="mb-4 text-white fw-bold text-shadow">Admin Dashboard</h2>
    
    <div class="row">
        <div class="col-md-4">
            <div class="glass-card d-flex align-items-center justify-content-between">
                <div><h5 class="text-muted">Total Staff</h5><h2 class="fw-bold text-primary"><?php echo $r1['TOTAL']; ?></h2></div>
                <i class="fas fa-users fa-3x text-primary opacity-50"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card d-flex align-items-center justify-content-between">
                <div><h5 class="text-muted">Fruit Variety</h5><h2 class="fw-bold text-success"><?php echo $r2['TOTAL']; ?></h2></div>
                <i class="fas fa-boxes fa-3x text-success opacity-50"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card d-flex align-items-center justify-content-between">
                <div><h5 class="text-muted">Today's Sales</h5><h2 class="fw-bold text-warning">RM <?php echo number_format($r3['TOTAL'] ?? 0, 2); ?></h2></div>
                <i class="fas fa-coins fa-3x text-warning opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-5">
            <div class="glass-card" style="height: 400px;">
                <h5 class="mb-3 text-center border-bottom pb-2">Fruit Category Distribution</h5>
                <div class="d-flex justify-content-center h-100"><canvas id="pieChart"></canvas></div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="glass-card" style="height: 400px;">
                <h5 class="mb-3 border-bottom pb-2">Top 5 Highest Stock</h5>
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: { labels: <?php echo json_encode($kategori_label); ?>, datasets: [{ data: <?php echo json_encode($kategori_data); ?>, backgroundColor: ['#36b9cc', '#1cc88a', '#4e73df', '#f6c23e'], borderWidth: 1 }] },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const ctxBar = document.getElementById('barChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: { labels: <?php echo json_encode($buah_label); ?>, datasets: [{ label: 'Stock Quantity', data: <?php echo json_encode($buah_stok); ?>, backgroundColor: 'rgba(78, 115, 223, 0.7)', borderColor: 'rgba(78, 115, 223, 1)', borderWidth: 1, borderRadius: 5 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
    });
</script>
</body>
</html>