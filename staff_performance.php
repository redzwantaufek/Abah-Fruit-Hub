<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

include('includes/header.php');

// SQL untuk dapatkan data prestasi staff
$sql = "SELECT s.StaffName, COUNT(o.OrderId) AS TOTAL_TRANS, SUM(o.TotalAmount) AS TOTAL_SALES
        FROM STAFFS s
        JOIN ORDERS o ON s.StaffId = o.StaffId
        GROUP BY s.StaffName
        ORDER BY TOTAL_SALES DESC";
$stmt = oci_parse($dbconn, $sql);
oci_execute($stmt);

$staff_names = [];
$sales_data = [];

// Simpan data dalam array untuk kegunaan carta
while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
    $rows[] = $row; // Simpan untuk table
    $staff_names[] = $row['STAFFNAME'];
    $sales_data[] = $row['TOTAL_SALES'];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold text-shadow">
            <i class="fas fa-medal me-2"></i>Staff Performance Report
        </h2>
        <button onclick="window.print()" class="btn btn-light d-print-none shadow-sm">
            <i class="fas fa-print me-2"></i>Print Report
        </button>
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="glass-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>Sales Collection Comparison (RM)</h5>
                <div style="height: 350px;">
                    <canvas id="staffChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="glass-card">
                <h5 class="fw-bold mb-3"><i class="fas fa-list-ol me-2 text-success"></i>Performance Data Table</h5>
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Rank</th>
                            <th>Staff Name</th>
                            <th class="text-center">Total Transactions</th>
                            <th class="text-end">Total Sales (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        if (!empty($rows)) {
                            foreach ($rows as $r) { 
                                $medal = "";
                                if($rank == 1) $medal = " <i class='fas fa-trophy text-warning'></i>";
                        ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><strong><?php echo htmlspecialchars($r['STAFFNAME']) . $medal; ?></strong></td>
                            <td class="text-center"><?php echo $r['TOTAL_TRANS']; ?></td>
                            <td class="text-end fw-bold text-primary">RM <?php echo number_format($r['TOTAL_SALES'], 2); ?></td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-4'>No sales data recorded yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<script>
    const ctxStaff = document.getElementById('staffChart').getContext('2d');
    new Chart(ctxStaff, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($staff_names); ?>,
            datasets: [{
                label: 'Total Sales (RM)',
                data: <?php echo json_encode($sales_data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });
</script>

</body>
</html>