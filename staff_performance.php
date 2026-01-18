<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 

// 1. Dapatkan Data Prestasi
// NOTA: Saya buang 'WHERE s.StaffRole != ADMIN' supaya Admin pun masuk dalam list!
$q = "SELECT s.StaffName, COUNT(o.OrderId) as TotalTxn, NVL(SUM(o.TotalAmount), 0) as TotalSales 
      FROM STAFFS s
      LEFT JOIN ORDERS o ON s.StaffId = o.StaffId
      GROUP BY s.StaffName 
      ORDER BY TotalSales DESC";

$stmt = oci_parse($dbconn, $q);
oci_execute($stmt);

// Sediakan data untuk Chart & Table
$labels = [];
$data = [];
$table_rows = [];
$rank = 1;

while ($r = oci_fetch_assoc($stmt)) {
    $labels[] = $r['STAFFNAME'];
    $data[]   = $r['TOTALSALES'];
    
    // Simpan data untuk table
    $r['RANK'] = $rank++;
    $table_rows[] = $r;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-chart-line me-2"></i>Staff Performance</h2>
        <a href="report_list.php" class="btn btn-light text-primary fw-bold shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="glass-card p-4 mb-4">
        <h5 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="fas fa-trophy text-warning me-2"></i>Sales Collection Comparison (RM)</h5>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="staffChart"></canvas>
        </div>
    </div>

    <div class="glass-card p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-list-ol text-primary me-2"></i>Performance Leaderboard</h5>
            <small class="text-muted d-print-none">Updated: <?php echo date('d M Y, h:i A'); ?></small>
        </div>

        <div class="row mb-3 align-items-center justify-content-between d-print-none">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Show</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                </select>
                <span class="text-muted fw-bold small ms-2">staff</span>
            </div>
            <div class="col-md-4">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search staff name...">
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tablePerf">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 80px;">Rank</th>
                    <th>Staff Name</th>
                    <th class="text-center">Total Transactions</th>
                    <th class="text-end">Total Sales (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($table_rows as $row) { 
                    $rankIcon = "";
                    $rowClass = "";
                    // Highlight Top 3
                    if ($row['RANK'] == 1) { 
                        $rankIcon = "🥇"; 
                        $rowClass = "fw-bold bg-warning bg-opacity-10"; 
                    }
                    elseif ($row['RANK'] == 2) { $rankIcon = "🥈"; }
                    elseif ($row['RANK'] == 3) { $rankIcon = "🥉"; }
                ?>
                <tr class="<?php echo $rowClass; ?>">
                    <td class="text-center">
                        <?php echo $rankIcon ? "<span class='fs-5'>$rankIcon</span>" : "<span class='badge bg-secondary rounded-circle'>".$row['RANK']."</span>"; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-white border rounded-circle p-1 me-2 d-flex justify-content-center align-items-center" style="width:35px;height:35px;">
                                <i class="fas fa-user text-muted"></i>
                            </div>
                            <span><?php echo htmlspecialchars($row['STAFFNAME']); ?></span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border px-3"><?php echo $row['TOTALTXN']; ?> Orders</span>
                    </td>
                    <td class="text-end text-success fw-bold">
                        RM <?php echo number_format($row['TOTALSALES'], 2); ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="text-end mt-4 d-print-none">
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow rounded-pill">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 1. Chart Setup
const ctx = document.getElementById('staffChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Total Sales (RM)',
            data: <?php echo json_encode($data); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            borderRadius: 5,
            barThickness: 50
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        scales: {
            y: { 
                beginAtZero: true, 
                suggestedMax: 100, // Supaya graf tak nampak pelik kalau sales sikit
                grid: { color: '#f0f0f0' } 
            },
            x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } }
    }
});

// 2. DataTable Setup
$(document).ready(function() {
    var table = $('#tablePerf').DataTable({
        "dom": "rtip",
        "pageLength": 10,
        "ordering": false,
        "language": {
            "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ staff</span>",
            "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
        }
    });

    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
    $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
});
</script>
</body>
</html>