<?php
session_start();
require_once('db_conn.php');

// --- SECURITY CHECK: LOGIN REQUIRED ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// --------------------------------------

include('includes/header.php');

if (isset($_POST['submit_sale'])) {
    $cust_id = $_POST['cust_id'];
    $fruit_id = $_POST['fruit_id'];
    $qty = $_POST['quantity'];
    $pay_method = $_POST['payment_method'];
    $staff_id = $_SESSION['user_id']; 

    // Check Stok
    $sql_check = "SELECT FruitPrice, QuantityStock FROM FRUITS WHERE FruitId = :fid";
    $s_check = oci_parse($dbconn, $sql_check);
    oci_bind_by_name($s_check, ":fid", $fruit_id);
    oci_execute($s_check);
    $fruit_data = oci_fetch_array($s_check, OCI_ASSOC);

    if ($fruit_data) {
        $price = $fruit_data['FRUITPRICE'];
        $current_stock = $fruit_data['QUANTITYSTOCK'];

        if ($qty > $current_stock) {
            echo "<script>Swal.fire('Stok Tak Cukup!', 'Baki stok hanya: $current_stock', 'warning');</script>";
        } else {
            $total_amount = $price * $qty;

            // 1. Insert Orders
            $sql_ord = "INSERT INTO ORDERS (OrderId, OrderDate, CustId, StaffId, TotalAmount, PaymentMethod, OrderStatus) 
                        VALUES (order_seq.NEXTVAL, SYSDATE, :cid, :sid, :tot, :pay, 'COMPLETED')
                        RETURNING OrderId INTO :new_oid";
            
            $stmt_ord = oci_parse($dbconn, $sql_ord);
            $new_order_id = 0;
            oci_bind_by_name($stmt_ord, ":cid", $cust_id);
            oci_bind_by_name($stmt_ord, ":sid", $staff_id);
            oci_bind_by_name($stmt_ord, ":tot", $total_amount);
            oci_bind_by_name($stmt_ord, ":pay", $pay_method);
            oci_bind_by_name($stmt_ord, ":new_oid", $new_order_id, -1, SQLT_INT);
            oci_execute($stmt_ord, OCI_NO_AUTO_COMMIT);

            // 2. Insert OrderDetails
            $sql_dtl = "INSERT INTO ORDERDETAILS (OrderDetailsId, OrderId, FruitId, Quantity)
                        VALUES (orderdtl_seq.NEXTVAL, :oid, :fid, :qty)";
            $stmt_dtl = oci_parse($dbconn, $sql_dtl);
            oci_bind_by_name($stmt_dtl, ":oid", $new_order_id);
            oci_bind_by_name($stmt_dtl, ":fid", $fruit_id);
            oci_bind_by_name($stmt_dtl, ":qty", $qty);
            oci_execute($stmt_dtl, OCI_NO_AUTO_COMMIT);

            // 3. Update Stock
            $sql_upd = "UPDATE FRUITS SET QuantityStock = QuantityStock - :qty WHERE FruitId = :fid";
            $stmt_upd = oci_parse($dbconn, $sql_upd);
            oci_bind_by_name($stmt_upd, ":qty", $qty);
            oci_bind_by_name($stmt_upd, ":fid", $fruit_id);
            oci_execute($stmt_upd, OCI_NO_AUTO_COMMIT);

            // 4. Commit or Rollback
            $res = oci_commit($dbconn);
            
            if ($res) {
                // --- SWEETALERT SUCCESS ---
                echo "
                <script>
                    Swal.fire({
                        title: 'Transaksi Berjaya!',
                        text: 'Resit #' + $new_order_id + ' telah direkodkan.',
                        icon: 'success',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        window.location = 'orders.php';
                    });
                </script>";
            } else {
                oci_rollback($dbconn);
                echo "<script>Swal.fire('Ralat', 'Transaksi Gagal!', 'error');</script>";
            }
        }
    }
}
?>

<div class="container mt-4">
    <div class="card card-custom p-4 mx-auto" style="max-width: 600px;">
        <h3 class="mb-3"><i class="fas fa-cash-register text-success"></i> Buat Jualan Baru</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label>Pelanggan</label>
                <select name="cust_id" class="form-select" required>
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php
                    $s1 = oci_parse($dbconn, "SELECT CustId, CustName FROM CUSTOMER ORDER BY CustName");
                    oci_execute($s1);
                    while ($r1 = oci_fetch_array($s1, OCI_ASSOC)) {
                        echo "<option value='".$r1['CUSTID']."'>".$r1['CUSTNAME']."</option>";
                    }
                    ?>
                </select>
                <small><a href="customer_add.php">Daftar Pelanggan Baru?</a></small>
            </div>

            <div class="mb-3">
                <label>Pilih Buah</label>
                <select name="fruit_id" class="form-select" required>
                    <option value="">-- Pilih Buah --</option>
                    <?php
                    $s2 = oci_parse($dbconn, "SELECT FruitId, FruitName, FruitPrice, QuantityStock FROM FRUITS WHERE QuantityStock > 0 ORDER BY FruitName");
                    oci_execute($s2);
                    while ($r2 = oci_fetch_array($s2, OCI_ASSOC)) {
                        echo "<option value='".$r2['FRUITID']."'>".$r2['FRUITNAME']." (RM ".$r2['FRUITPRICE']." | Stok: ".$r2['QUANTITYSTOCK'].")</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="row">
                <div class="col">
                    <label>Kuantiti</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>
                <div class="col">
                    <label>Cara Bayaran</label>
                    <select name="payment_method" class="form-select">
                        <option value="CASH">Tunai (Cash)</option>
                        <option value="QR">QR Pay</option>
                        <option value="CARD">Kad Debit/Kredit</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="submit_sale" class="btn btn-success w-100 mt-4 btn-lg">Sahkan Pembelian</button>
        </form>
    </div>
</div>