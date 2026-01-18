<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// SQL: Group by Date & Calculate Totals
$sql = "SELECT TRUNC(OrderDate) as SaleDate, COUNT(OrderId) as TotalTxn, SUM(TotalAmount) as DailyTotal 
        FROM ORDERS 
        WHERE TO_CHAR(OrderDate, 'MM') = :mth AND TO_CHAR(OrderDate, 'YYYY') = :yr
        GROUP BY TRUNC(OrderDate) 
        ORDER BY SaleDate DESC";

$stmt = oci_parse($dbconn, $sql);
oci_bind_by_name($stmt, ":mth", $selected_month);
oci_bind_by_name($stmt, ":yr", $selected_year);
oci_execute($stmt);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-calendar-check me-2"></i>Monthly Sales Report</h2>
        <a href="report_list.php" class="btn btn-light text-primary fw-bold shadow-sm rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="glass-card p-4 mb-4 d-print-none">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="fw-bold small text-muted">Month</label>
                <select name="month" class="form-select shadow-sm">
                    <?php
                    for ($m=1; $m<=12; $m++) {
                        $val = str_pad($m, 2, "0", STR_PAD_LEFT);
                        $name = date('F', mktime(0,0,0,$m,10));
                        $sel = ($val == $selected_month) ? 'selected' : '';
                        echo "<option value='$val' $sel>$name</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="fw-bold small text-muted">Year</label>
                <select name="year" class="form-select shadow-sm">
                    <?php
                    for ($y=date('Y'); $y>=2024; $y--) {
                        $sel = ($y == $selected_year) ? 'selected' : '';
                        echo "<option value='$y' $sel>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm rounded-pill">Generate Report</button>
            </div>
        </form>
    </div>

    <div class="glass-card p-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase text-dark"><i class="fas fa-file-invoice me-2"></i>Sales Report</h2>
            <p class="text-muted">Period: <strong><?php echo date('F', mktime(0,0,0,$selected_month,10)) . " " . $selected_year; ?></strong></p>
        </div>

        <div class="row mb-4 align-items-center justify-content-between d-print-none">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Show</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px;">
                    <option value="10">10</option>
                    <option value="31">31</option>
                </select>
                <span class="text-muted fw-bold small ms-2">days</span>
            </div>
            <div class="col-md-5">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search date...">
                </div>
            </div>
        </div>

        <table class="table table-bordered align-middle w-100 no-search" id="tableReport">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Date</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-end">Daily Revenue (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grand_total = 0;
                $total_txn = 0;
                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $grand_total += $row['DAILYTOTAL'];
                    $total_txn += $row['TOTALTXN'];
                    echo "<tr>";
                    echo "<td class='text-center'>" . date('d M Y', strtotime($row['SALEDATE'])) . "</td>";
                    echo "<td class='text-center'><span class='badge bg-info text-dark rounded-pill px-3'>" . $row['TOTALTXN'] . " Orders</span></td>";
                    echo "<td class='text-end fw-bold'>RM " . number_format($row['DAILYTOTAL'], 2) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <td class="text-end fw-bold text-uppercase">Grand Total</td>
                    <td class="text-center fw-bold"><?php echo $total_txn; ?> Orders</td>
                    <td class="text-end fs-5 fw-bold text-success">RM <?php echo number_format($grand_total, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-end mt-4 d-print-none">
            <button onclick="window.print()" class="btn btn-success fw-bold px-4 shadow rounded-pill"><i class="fas fa-print me-2"></i> Print Report</button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#tableReport').DataTable({
            "dom": "rtip", // Disable default controls
            "pageLength": 10,
            "ordering": false, // Disable sorting for reports usually
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ days</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });
</script>
</body>
</html>