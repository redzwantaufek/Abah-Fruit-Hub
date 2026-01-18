<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];

    $sql = "INSERT INTO CUSTOMER (CustId, CustName, CustEmail, CustPhone, CustAddress) 
            VALUES (cust_id_seq.NEXTVAL, :nm, :em, :ph, :ad)";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":ad", $addr);

    if (oci_execute($stmt)) {
        echo "<script>Swal.fire({ title: 'Success!', text: 'Customer Registered.', icon: 'success' }).then(() => { window.location = 'customer.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-3 text-success fw-bold">➕ Add New Customer</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Customer Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label>Phone No.</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-success w-100 fw-bold">Save Customer</button>
            <a href="customer.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>