<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

if (!isset($_GET['id'])) { header("Location: customer.php"); exit(); }
$cid = $_GET['id'];

// Get Data
$stmt = oci_parse($dbconn, "SELECT * FROM CUSTOMER WHERE CustId = :id");
oci_bind_by_name($stmt, ":id", $cid);
oci_execute($stmt);
$row = oci_fetch_array($stmt, OCI_ASSOC);

if (!$row) { echo "<script>window.location='customer.php';</script>"; exit(); }

// Update Logic
if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $sql = "UPDATE CUSTOMER SET CustName=:nm, CustPhone=:ph, CustEmail=:em, CustAddress=:ad WHERE CustId=:id";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":ad", $address);
    oci_bind_by_name($stmt, ":id", $cid);

    if (oci_execute($stmt)) {
        echo "<script>Swal.fire('Success', 'Customer details updated.', 'success').then(() => { window.location = 'customer.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h3 class="fw-bold text-primary mb-4"><i class="fas fa-user-edit me-2"></i>Edit Customer</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label class="fw-bold">Customer Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['CUSTNAME']); ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['CUSTPHONE']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['CUSTEMAIL']); ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($row['CUSTADDRESS']); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-primary fw-bold shadow flex-grow-1">
                    <i class="fas fa-save me-2"></i> Update Customer
                </button>
                <a href="customer.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>