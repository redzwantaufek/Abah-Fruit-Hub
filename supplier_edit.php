<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

if (!isset($_GET['id'])) { header("Location: supplier.php"); exit(); }
$sid = $_GET['id'];

// Get Data
$sql = "SELECT s.*, l.FarmAddress, d.LogisticPartner, d.DistributionCenterId 
        FROM SUPPLIER s
        LEFT JOIN LOCALFARM l ON s.SupplierId = l.SupplierId
        LEFT JOIN DISTRIBUTOR d ON s.SupplierId = d.SupplierId
        WHERE s.SupplierId = :id";
$stmt = oci_parse($dbconn, $sql);
oci_bind_by_name($stmt, ":id", $sid);
oci_execute($stmt);
$row = oci_fetch_array($stmt, OCI_ASSOC);

if (!$row) { echo "<script>window.location='supplier.php';</script>"; exit(); }

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];
    
    // Update Parent
    $sql_upd = "UPDATE SUPPLIER SET SupplierName=:nm, SupplierContact=:con, SupplierPhone=:ph WHERE SupplierId=:id";
    $stmt_upd = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt_upd, ":nm", $name);
    oci_bind_by_name($stmt_upd, ":con", $contact);
    oci_bind_by_name($stmt_upd, ":ph", $phone);
    oci_bind_by_name($stmt_upd, ":id", $sid);
    
    if (oci_execute($stmt_upd, OCI_NO_AUTO_COMMIT)) {
        $success = true;
        
        // Update Child based on type
        if ($row['SUPPLIERTYPE'] == 'LOCALFARM') {
            $f_addr = $_POST['farm_address'];
            $c_upd = oci_parse($dbconn, "UPDATE LOCALFARM SET FarmAddress=:addr WHERE SupplierId=:id");
            oci_bind_by_name($c_upd, ":addr", $f_addr);
            oci_bind_by_name($c_upd, ":id", $sid);
            if(!oci_execute($c_upd, OCI_NO_AUTO_COMMIT)) $success = false;
        } else {
            $lp = $_POST['log_partner'];
            $dc = $_POST['center_id'];
            $c_upd = oci_parse($dbconn, "UPDATE DISTRIBUTOR SET LogisticPartner=:lp, DistributionCenterId=:dc WHERE SupplierId=:id");
            oci_bind_by_name($c_upd, ":lp", $lp);
            oci_bind_by_name($c_upd, ":dc", $dc);
            oci_bind_by_name($c_upd, ":id", $sid);
            if(!oci_execute($c_upd, OCI_NO_AUTO_COMMIT)) $success = false;
        }

        if ($success) {
            oci_commit($dbconn);
            echo "<script>Swal.fire('Updated', 'Supplier details updated.', 'success').then(() => { window.location = 'supplier.php'; });</script>";
        } else {
            oci_rollback($dbconn);
            echo "<script>Swal.fire('Error', 'Failed to update details.', 'error');</script>";
        }
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h3 class="fw-bold text-primary mb-4"><i class="fas fa-edit me-2"></i>Edit Supplier</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label class="fw-bold">Company Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['SUPPLIERNAME']); ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Contact Person</label>
                    <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($row['SUPPLIERCONTACT']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['SUPPLIERPHONE']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Supplier Type</label>
                <input type="text" class="form-control bg-light" value="<?php echo $row['SUPPLIERTYPE']; ?>" readonly>
            </div>

            <?php if ($row['SUPPLIERTYPE'] == 'LOCALFARM') { ?>
                <div class="mb-4">
                    <label class="fw-bold">Farm Address</label>
                    <textarea name="farm_address" class="form-control" rows="2"><?php echo htmlspecialchars($row['FARMADDRESS']); ?></textarea>
                </div>
            <?php } else { ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="fw-bold">Logistic Partner</label>
                        <input type="text" name="log_partner" class="form-control" value="<?php echo htmlspecialchars($row['LOGISTICPARTNER']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Distribution Center ID</label>
                        <input type="text" name="center_id" class="form-control" value="<?php echo htmlspecialchars($row['DISTRIBUTIONCENTERID']); ?>">
                    </div>
                </div>
            <?php } ?>

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-primary fw-bold shadow flex-grow-1">
                    <i class="fas fa-save me-2"></i> Update Changes
                </button>
                <a href="supplier.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>