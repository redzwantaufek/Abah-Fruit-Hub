<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: orders.php"); exit(); }
include('includes/header.php');

$order_id = $_GET['id'];

// Get Header Info
$sql_head = "SELECT o.OrderId, o.OrderDate, o.TotalAmount, o.PaymentMethod, c.CustName, c.CustPhone, s.StaffName
             FROM ORDERS o JOIN CUSTOMER c ON o.CustId = c.CustId
             LEFT JOIN STAFFS s ON o.StaffId = s.StaffId WHERE o.OrderId = :oid";
$stmt = oci_parse($dbconn, $sql_head); 
oci_bind_by_name($stmt, ":oid", $order_id); 
oci_execute($stmt);
$header = oci_fetch_array($stmt, OCI_ASSOC);

if (!$header) { echo "<div class='container mt-5'><div class='alert alert-danger'>Order not found!</div></div>"; exit(); }
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <a href="orders.php" class="btn btn-light shadow-sm fw-bold"><i class="fas fa-arrow-left me-2"></i> Back to History</a>
        <button onclick="window.print()" class="btn btn-success fw-bold shadow"><i class="fas fa-print me-2"></i> Print Receipt</button>
    </div>
    
    <div class="glass-card mx-auto p-5" style="max-width: 800px; border-top: 5px solid #198754;">
        <div class="text-center mb-4 border-bottom pb-3">
            <h2 class="fw-bold text-success"><i class="fas fa-apple-alt me-2"></i>FruitHub</h2>
            <p class="text-muted mb-0">Official Sales Receipt</p>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <h5 class="fw-bold text-secondary">Billed To:</h5>
                <p class="mb-0 fw-bold fs-5"><?php echo $header['CUSTNAME']; ?></p>
                <p class="text-muted small"><i class="fas fa-phone me-1"></i> <?php echo $header['CUSTPHONE']; ?></p>
            </div>
            <div class="col-6 text-end">
                <h5 class="fw-bold text-secondary">Order Info:</h5>
                <p class="mb-0"><strong>Order ID:</strong> #<?php echo $header['ORDERID']; ?></p>
                <p class="mb-0"><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($header['ORDERDATE'])); ?></p>
                <p class="mb-0"><strong>Served By:</strong> <?php echo $header['STAFFNAME']; ?></p>
                <p class="mb-0"><strong>Method:</strong> <span class="badge bg-secondary"><?php echo $header['PAYMENTMETHOD']; ?></span></p>
            </div>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">#</th>
                    <th>Item Description</th>
                    <th class="text-end" style="width: 100px;">Price</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-end" style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_detail = "SELECT d.Quantity, f.FruitName, f.FruitPrice FROM ORDERDETAILS d
                               JOIN FRUITS f ON d.FruitId = f.FruitId WHERE d.OrderId = :oid";
                $stmt2 = oci_parse($dbconn, $sql_detail); 
                oci_bind_by_name($stmt2, ":oid", $order_id); 
                oci_execute($stmt2);
                $bil = 1; 

                while ($row = oci_fetch_array($stmt2, OCI_ASSOC)) {
                    $subtotal = $row['FRUITPRICE'] * $row['QUANTITY'];
                    echo "<tr>";
                    echo "<td class='text-center text-muted'>" . $bil++ . "</td>";
                    echo "<td>" . $row['FRUITNAME'] . "</td>";
                    echo "<td class='text-end'>" . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td class='text-center'>" . $row['QUANTITY'] . "</td>";
                    echo "<td class='text-end fw-bold'>RM " . number_format($subtotal, 2) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end text-uppercase small text-muted pt-3">Total Amount</td>
                    <td class="text-end fs-4 fw-bold text-primary border-0 pt-2">RM <?php echo number_format($header['TOTALAMOUNT'], 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-5 pt-3 border-top text-muted small">
            <p class="mb-1">Thank you for shopping with FruitHub!</p>
            <p>Please come again.</p>
        </div>
    </div>
</div>
</body>
</html>