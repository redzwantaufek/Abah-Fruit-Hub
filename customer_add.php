<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

$msg = "";

if (isset($_POST['save_cust'])) {
    $n = $_POST['name'];
    $p = $_POST['phone'];
    $e = $_POST['email'];
    $a = $_POST['address'];

    // Guna cust_id_seq
    $q = "INSERT INTO CUSTOMER (CustId, CustName, CustPhone, CustEmail, CustAddress) 
          VALUES (cust_id_seq.NEXTVAL, :nm, :ph, :em, :ad)";
    
    $stmt = oci_parse($dbconn, $q);
    oci_bind_by_name($stmt, ":nm", $n);
    oci_bind_by_name($stmt, ":ph", $p);
    oci_bind_by_name($stmt, ":em", $e);
    oci_bind_by_name($stmt, ":ad", $a);

    if (oci_execute($stmt)) {
        $msg = "Swal.fire('Success', 'Customer added.', 'success').then(() => { window.location = 'customer.php'; });";
    } else {
        $err = oci_error($stmt);
        $msg = "Swal.fire('Error', '" . $err['message'] . "', 'error');";
    }
}
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h4 class="fw-bold text-primary mb-4"><i class="fas fa-user-plus me-2"></i>New Customer</h4>
        
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold">Customer Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Full Name">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Phone No.</label>
                    <input type="text" name="phone" class="form-control" required placeholder="01x-xxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="Required for Unique ID">
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="3" required></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="save_cust" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">
                    Save Customer
                </button>
                <a href="customer.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>