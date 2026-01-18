<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

$oid = $_GET['id'] ?? null;
if (!$oid) { header("Location: orders.php"); exit(); }

// Get Order Info (Join Customer & Staff)
$q_head = "SELECT o.*, c.CustName, c.CustPhone, s.StaffName 
           FROM ORDERS o
           JOIN CUSTOMER c ON o.CustId = c.CustId
           LEFT JOIN STAFFS s ON o.StaffId = s.StaffId
           WHERE o.OrderId = :oid";
$s_head = oci_parse($dbconn, $q_head);
oci_bind_by_name($s_head, ":oid", $oid);
oci_execute($s_head);
$ord = oci_fetch_array($s_head, OCI_ASSOC);

if (!$ord) die("<script>alert('Order not found'); window.location='orders.php';</script>");

// Get Items
$q_items = "SELECT od.*, f.FruitName, f.FruitPrice 
            FROM ORDERDETAILS od
            JOIN FRUITS f ON od.FruitId = f.FruitId
            WHERE od.OrderId = :oid";
$s_items = oci_parse($dbconn, $q_items);
oci_bind_by_name($s_items, ":oid", $oid);
oci_execute($s_items);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <a href="orders.php" class="btn btn-light shadow-sm fw-bold"><i class="fas fa-arrow-left me-2"></i> Back</a>
        <button onclick="window.print()" class="btn btn-primary fw-bold shadow"><i class="fas fa-print me-2"></i> Print Receipt</button>
    </div>
    
    <div class="glass-card mx-auto p-5" style="max-width: 800px; border-top: 5px solid #23a6d5;">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-0"><i class="fas fa-apple-alt text-success me-2"></i>FruitHub</h2>
            <p class="text-muted small mb-0">No 123, Jalan Buah, Melaka.</p>
            <p class="text-muted small">Tel: 06-1234567</p>
        </div>

        <div class="row mb-4 border-bottom pb-3">
            <div class="col-6">
                <small class="text-muted fw-bold">BILLED TO:</small><br>
                <h5 class="fw-bold mb-0"><?php echo $ord['CUSTNAME']; ?></h5>
                <small><?php echo $ord['CUSTPHONE']; ?></small>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted fw-bold">ORDER INFO:</small><br>
                <span class="fw-bold">#<?php echo $ord['ORDERID']; ?></span><br>
                <small><?php echo date('d M Y, h:i A', strtotime($ord['ORDERDATE'])); ?></small><br>
                <small class="badge bg-light text-dark border mt-1">Served by: <?php echo $ord['STAFFNAME']; ?></small>
            </div>
        </div>

        <table class="table table-borderless">
            <thead class="border-bottom">
                <tr class="text-secondary small">
                    <th>ITEM</th>
                    <th class="text-center">QTY</th>
                    <th class="text-end">UNIT PRICE</th>
                    <th class="text-end">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($item = oci_fetch_array($s_items, OCI_ASSOC)) {
                    $subtotal = $item['QUANTITY'] * $item['FRUITPRICE'];
                    echo "<tr>";
                    echo "<td>" . $item['FRUITNAME'] . "</td>";
                    echo "<td class='text-center'>" . $item['QUANTITY'] . "</td>";
                    echo "<td class='text-end'>" . number_format($item['FRUITPRICE'], 2) . "</td>";
                    echo "<td class='text-end fw-bold'>" . number_format($subtotal, 2) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
            <tfoot class="border-top mt-3">
                <tr>
                    <td colspan="3" class="text-end fw-bold pt-4">GRAND TOTAL</td>
                    <td class="text-end fw-bold fs-4 text-primary pt-3">RM <?php echo number_format($ord['TOTALAMOUNT'], 2); ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="text-center mt-5 pt-4 text-muted small border-top">
            <p class="mb-0">Thank you for shopping with us!</p>
            <p>Please come again.</p>
        </div>
    </div>
</div>
</body>
</html>