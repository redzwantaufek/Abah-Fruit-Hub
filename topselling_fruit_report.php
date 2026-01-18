<?php
session_start();
require_once('db_conn.php');
include('includes/header.php');

$sql = "SELECT f.FruitName, SUM(od.Quantity) AS QtySold
        FROM ORDERDETAILS od
        JOIN FRUITS f ON od.FruitId = f.FruitId
        GROUP BY f.FruitName
        ORDER BY QtySold DESC
        FETCH FIRST 5 ROWS ONLY";
$stmt = oci_parse($dbconn, $sql);
oci_execute($stmt);

$labels = []; $data = [];
while($row = oci_fetch_array($stmt, OCI_ASSOC)){
    $labels[] = $row['FRUITNAME'];
    $data[] = $row['QTYSOLD'];
}
?>

<div class="container-fluid">
    <div class="glass-card">
        <h3 class="fw-bold mb-4 text-center">Top 5 Best-Selling Fruits</h3>
        <div style="height: 400px; width: 100%;">
            <canvas id="topFruitsChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('topFruitsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Total Quantity Sold',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(25, 135, 84, 0.7)',
            borderColor: '#198754',
            borderWidth: 2,
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});
</script>