<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php'); 

$selected_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// SQL Reports... (Kekalkan logic SQL anda)
$sql_report = "SELECT f.FruitName, SUM(od.Quantity) AS TotalQty, f.FruitPrice, SUM(od.Quantity * f.FruitPrice) AS TotalRevenue
               FROM ORDERS o
               JOIN ORDERDETAILS od ON o.OrderId = od.OrderId
               JOIN FRUITS f ON od.FruitId = f.FruitId
               WHERE TO_CHAR(o.OrderDate, 'MM') = :mth AND TO_CHAR(o.OrderDate, 'YYYY') = :yr
               GROUP BY f.FruitName, f.FruitPrice
               ORDER BY TotalRevenue DESC";
$stmt = oci_parse($dbconn, $sql_report);
oci_bind_by_name($stmt, ":mth", $selected_month);
oci_bind_by_name($stmt, ":yr", $selected_year);
oci_execute($stmt);

// Calculate Grand Total
$grand_total = 0;
?>

<div class="container-fluid">
    <div class="glass-card p-4 mb-4 d-print-none">
        <h4 class="fw-bold mb-3 text-primary"><i class="fas fa-filter me-2"></i>Report Filter</h4>
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="fw-bold small text-muted">Month</label>
                <select name="month" class="form-select shadow-sm">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $val = str_pad($m, 2, "0", STR_PAD_LEFT);
                        $name = date('F', mktime(0, 0, 0, $m, 10));
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
                    for ($y = date('Y'); $y >= 2020; $y--) {
                        $sel = ($y == $selected_year) ? 'selected' : '';
                        echo "<option value='$y' $sel>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">Generate Report</button>
            </div>
        </form>
    </div>

    <div class="glass-card p-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase"><i class="fas fa-file-invoice-dollar me-2 text-success"></i>Monthly Sales Report</h2>
            <p class="text-muted mb-0">FruitHub Management System</p>
            <p class="fw-bold text-primary">Period: <?php echo date('F', mktime(0,0,0,$selected_month,10)) . " " . $selected_year; ?></p>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No.</th>
                    <th>Fruit Name</th>
                    <th class="text-center">Qty Sold</th>
                    <th class="text-end">Unit Price (RM)</th>
                    <th class="text-end">Total Revenue (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $bil = 1;
                $rows = []; // Simpan data untuk kira grand total manual jika perlu
                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $grand_total += $row['TOTALREVENUE'];
                    echo "<tr>";
                    echo "<td class='text-center'>" . $bil++ . "</td>";
                    echo "<td>" . $row['FRUITNAME'] . "</td>";
                    echo "<td class='text-center'>" . $row['TOTALQTY'] . "</td>";
                    echo "<td class='text-end'>" . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td class='text-end fw-bold'>RM " . number_format($row['TOTALREVENUE'], 2) . "</td>";
                    echo "</tr>";
                }
                if ($bil == 1) { echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No sales data found for this period.</td></tr>"; }
                ?>
            </tbody>
            <tfoot>
                <tr class="table-primary">
                    <td colspan="4" class="text-end fw-bold text-uppercase">Grand Total Sales</td>
                    <td class="text-end fs-5 fw-bold text-success">RM <?php echo number_format($grand_total, 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 d-flex justify-content-between align-items-center">
            <small class="text-muted">Generated on: <?php echo date('d/m/Y h:i A'); ?> by <?php echo $_SESSION['user_role']; ?></small>
            
            <button onclick="window.print()" class="btn btn-success fw-bold px-4 shadow d-print-none">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>
    </div>
</div>
</body>
</html>