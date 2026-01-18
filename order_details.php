<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: orders.php"); exit(); }
include('includes/header.php');

$order_id = $_GET['id'];

// Get Header
$sql_head = "SELECT o.OrderId, o.OrderDate, o.TotalAmount, c.CustName, s.StaffName
             FROM ORDERS o JOIN CUSTOMER c ON o.CustId = c.CustId
             LEFT JOIN STAFFS s ON o.StaffId = s.StaffId WHERE o.OrderId = :oid";
$stmt = oci_parse($dbconn, $sql_head); oci_bind_by_name($stmt, ":oid", $order_id); oci_execute($stmt);
$header = oci_fetch_array($stmt, OCI_ASSOC);

if (!$header) { echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found!</div></div>"; exit(); }
?>

<div class="container mt-4">
    <a href="orders.php" class="btn btn-light mb-3 shadow"><i class="fas fa-arrow-left"></i> Back</a>
    <button onclick="window.print()" class="btn btn-success mb-3 ms-2 shadow"><i class="fas fa-print"></i> Print Receipt</button>
    
    <div class="glass-card">
        <div class="row mb-4 border-bottom pb-3">
            <div class="col-md-6">
                <h4 class="text-primary fw-bold">Order #<?php echo $header['ORDERID']; ?></h4>
                <p class="text-muted mb-0">Date: <?php echo date('d-m-Y', strtotime($header['ORDERDATE'])); ?></p>
            </div>
            <div class="col-md-6 text-end">
                <h5 class="fw-bold"><?php echo $header['CUSTNAME']; ?></h5>
                <p class="mb-0">Served by: <?php echo $header['STAFFNAME']; ?></p>
            </div>
        </div>

        <h5 class="mb-3">Items Purchased:</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>No.</th><th>Fruit Name</th><th>Unit Price</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php
                $sql_detail = "SELECT d.Quantity, f.FruitName, f.FruitPrice FROM ORDERDETAILS d
                               JOIN FRUITS f ON d.FruitId = f.FruitId WHERE d.OrderId = :oid";
                $stmt2 = oci_parse($dbconn, $sql_detail); oci_bind_by_name($stmt2, ":oid", $order_id); oci_execute($stmt2);
                $bil = 1; $grand_total = 0;

                while ($row = oci_fetch_array($stmt2, OCI_ASSOC)) {
                    $subtotal = $row['FRUITPRICE'] * $row['QUANTITY'];
                    $grand_total += $subtotal;
                    echo "<tr>";
                    echo "<td>" . $bil++ . "</td>";
                    echo "<td>" . $row['FRUITNAME'] . "</td>";
                    echo "<td>RM " . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td>" . $row['QUANTITY'] . "</td>";
                    echo "<td>RM " . number_format($subtotal, 2) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot class="table-dark">
                <tr><td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                <td><strong>RM <?php echo number_format($grand_total, 2); ?></strong></td></tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>