<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

if (isset($_POST['submit_sale'])) {
    $cust_id = $_POST['cust_id'];
    $fruit_id = $_POST['fruit_id'];
    $qty = $_POST['quantity'];
    $pay_method = $_POST['payment_method'];
    $staff_id = $_SESSION['user_id']; 

    $sql_check = "SELECT FruitPrice, QuantityStock FROM FRUITS WHERE FruitId = :fid";
    $s_check = oci_parse($dbconn, $sql_check);
    oci_bind_by_name($s_check, ":fid", $fruit_id);
    oci_execute($s_check);
    $fruit_data = oci_fetch_array($s_check, OCI_ASSOC);

    if ($fruit_data) {
        $price = $fruit_data['FRUITPRICE'];
        $current_stock = $fruit_data['QUANTITYSTOCK'];

        if ($qty > $current_stock) {
            echo "<script>Swal.fire('Insufficient Stock!', 'Only $current_stock items remaining.', 'warning');</script>";
        } else {
            $total_amount = $price * $qty;
            $new_order_id = 0;

            $sql_ord = "INSERT INTO ORDERS (OrderId, OrderDate, CustId, StaffId, TotalAmount, PaymentMethod, OrderStatus) 
                        VALUES (order_seq.NEXTVAL, SYSDATE, :cid, :sid, :tot, :pay, 'COMPLETED') RETURNING OrderId INTO :new_oid";
            $stmt_ord = oci_parse($dbconn, $sql_ord);
            oci_bind_by_name($stmt_ord, ":cid", $cust_id);
            oci_bind_by_name($stmt_ord, ":sid", $staff_id);
            oci_bind_by_name($stmt_ord, ":tot", $total_amount);
            oci_bind_by_name($stmt_ord, ":pay", $pay_method);
            oci_bind_by_name($stmt_ord, ":new_oid", $new_order_id, -1, SQLT_INT);
            oci_execute($stmt_ord, OCI_NO_AUTO_COMMIT);

            $sql_dtl = "INSERT INTO ORDERDETAILS (OrderDetailsId, OrderId, FruitId, Quantity) VALUES (orderdtl_seq.NEXTVAL, :oid, :fid, :qty)";
            $stmt_dtl = oci_parse($dbconn, $sql_dtl);
            oci_bind_by_name($stmt_dtl, ":oid", $new_order_id);
            oci_bind_by_name($stmt_dtl, ":fid", $fruit_id);
            oci_bind_by_name($stmt_dtl, ":qty", $qty);
            oci_execute($stmt_dtl, OCI_NO_AUTO_COMMIT);

            $sql_upd = "UPDATE FRUITS SET QuantityStock = QuantityStock - :qty WHERE FruitId = :fid";
            $stmt_upd = oci_parse($dbconn, $sql_upd);
            oci_bind_by_name($stmt_upd, ":qty", $qty);
            oci_bind_by_name($stmt_upd, ":fid", $fruit_id);
            oci_execute($stmt_upd, OCI_NO_AUTO_COMMIT);

            if (oci_commit($dbconn)) {
                echo "<script>Swal.fire({ title: 'Transaction Successful!', text: 'Order #' + $new_order_id + ' recorded.', icon: 'success', confirmButtonColor: '#198754' }).then(() => { window.location = 'orders.php'; });</script>";
            } else {
                oci_rollback($dbconn);
                echo "<script>Swal.fire('Error', 'Transaction Failed!', 'error');</script>";
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="card card-custom p-4 mx-auto glass-card" style="max-width: 600px;">
        <h3 class="mb-3 text-white text-shadow"><i class="fas fa-cash-register"></i> New Sale / POS</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label class="fw-bold">Customer</label>
                <select name="cust_id" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php
                    $s1 = oci_parse($dbconn, "SELECT CustId, CustName FROM CUSTOMER ORDER BY CustName");
                    oci_execute($s1);
                    while ($r1 = oci_fetch_array($s1, OCI_ASSOC)) { echo "<option value='".$r1['CUSTID']."'>".$r1['CUSTNAME']."</option>"; }
                    ?>
                </select>
                <small><a href="customer_add.php" class="text-white">Register New Customer?</a></small>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Select Fruit</label>
                <select name="fruit_id" class="form-select" required>
                    <option value="">-- Select Fruit --</option>
                    <?php
                    $s2 = oci_parse($dbconn, "SELECT FruitId, FruitName, FruitPrice, QuantityStock FROM FRUITS WHERE QuantityStock > 0 ORDER BY FruitName");
                    oci_execute($s2);
                    while ($r2 = oci_fetch_array($s2, OCI_ASSOC)) { echo "<option value='".$r2['FRUITID']."'>".$r2['FRUITNAME']." (RM ".$r2['FRUITPRICE']." | Stock: ".$r2['QUANTITYSTOCK'].")</option>"; }
                    ?>
                </select>
            </div>

            <div class="row">
                <div class="col">
                    <label class="fw-bold">Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="col">
                    <label class="fw-bold">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option value="CASH">Cash</option>
                        <option value="QR">QR Pay</option>
                        <option value="CARD">Credit/Debit Card</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="submit_sale" class="btn btn-warning w-100 mt-4 btn-lg fw-bold">Confirm Purchase</button>
        </form>
    </div>
</div>
</body>
</html>