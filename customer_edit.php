<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

$cid = $_GET['id'] ?? null;
if (!$cid) { header("Location: customer.php"); exit(); }

$msg = "";

// Update Logic
if (isset($_POST['update_cust'])) {
    $q = "UPDATE CUSTOMER SET CustName=:nm, CustPhone=:ph, CustEmail=:em, CustAddress=:ad WHERE CustId=:id";
    $stmt = oci_parse($dbconn, $q);
    
    oci_bind_by_name($stmt, ":nm", $_POST['name']);
    oci_bind_by_name($stmt, ":ph", $_POST['phone']);
    oci_bind_by_name($stmt, ":em", $_POST['email']);
    oci_bind_by_name($stmt, ":ad", $_POST['address']);
    oci_bind_by_name($stmt, ":id", $cid);

    if (oci_execute($stmt)) {
        $msg = "Swal.fire('Success', 'Customer details updated.', 'success').then(() => { window.location = 'customer.php'; });";
    } else {
        $e = oci_error($stmt);
        $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
    }
}

// Get Data
$q_get = "SELECT * FROM CUSTOMER WHERE CustId = :id";
$s_get = oci_parse($dbconn, $q_get);
oci_bind_by_name($s_get, ":id", $cid);
oci_execute($s_get);
$row = oci_fetch_array($s_get, OCI_ASSOC);

if (!$row) die("<script>window.location='customer.php';</script>");
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h4 class="fw-bold text-primary mb-4"><i class="fas fa-user-edit me-2"></i>Edit Customer</h4>
        
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold">Customer Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['CUSTNAME']); ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Phone No.</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['CUSTPHONE']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['CUSTEMAIL']); ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($row['CUSTADDRESS']); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update_cust" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">
                    Save Changes
                </button>
                <a href="customer.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>