<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

$sid = $_GET['id'] ?? null;
if (!$sid) { header("Location: supplier.php"); exit(); }

$msg = "";

// Update Logic
if (isset($_POST['update_supp'])) {
    $q_upd = "UPDATE SUPPLIER SET SupplierName=:nm, SupplierContact=:con, SupplierPhone=:ph WHERE SupplierId=:id";
    $stmt = oci_parse($dbconn, $q_upd);
    oci_bind_by_name($stmt, ":nm", $_POST['name']);
    oci_bind_by_name($stmt, ":con", $_POST['contact']);
    oci_bind_by_name($stmt, ":ph", $_POST['phone']);
    oci_bind_by_name($stmt, ":id", $sid);
    
    if (oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
        $success = true;
        $type = $_POST['type_hidden']; // Hidden field for type
        
        if ($type == 'LOCALFARM') {
            $s2 = oci_parse($dbconn, "UPDATE LOCALFARM SET FarmAddress=:addr WHERE SupplierId=:id");
            oci_bind_by_name($s2, ":addr", $_POST['farm_address']);
            oci_bind_by_name($s2, ":id", $sid);
            if(!oci_execute($s2, OCI_NO_AUTO_COMMIT)) $success = false;
        } else {
            $s2 = oci_parse($dbconn, "UPDATE DISTRIBUTOR SET LogisticPartner=:lp, DistributionCenterId=:dc WHERE SupplierId=:id");
            oci_bind_by_name($s2, ":lp", $_POST['log_partner']);
            oci_bind_by_name($s2, ":dc", $_POST['center_id']);
            oci_bind_by_name($s2, ":id", $sid);
            if(!oci_execute($s2, OCI_NO_AUTO_COMMIT)) $success = false;
        }

        if ($success) {
            oci_commit($dbconn);
            $msg = "Swal.fire('Updated', 'Supplier details saved.', 'success').then(() => { window.location = 'supplier.php'; });";
        } else {
            oci_rollback($dbconn);
            $msg = "Swal.fire('Error', 'Update failed.', 'error');";
        }
    }
}

// Fetch Data
$q_get = "SELECT s.*, l.FarmAddress, d.LogisticPartner, d.DistributionCenterId 
          FROM SUPPLIER s
          LEFT JOIN LOCALFARM l ON s.SupplierId = l.SupplierId
          LEFT JOIN DISTRIBUTOR d ON s.SupplierId = d.SupplierId
          WHERE s.SupplierId = :id";
$s_get = oci_parse($dbconn, $q_get);
oci_bind_by_name($s_get, ":id", $sid);
oci_execute($s_get);
$supp = oci_fetch_array($s_get, OCI_ASSOC);
if (!$supp) die("<script>window.location='supplier.php';</script>");
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h4 class="fw-bold text-primary mb-4"><i class="fas fa-edit me-2"></i>Edit Supplier</h4>
        
        <form method="POST">
            <input type="hidden" name="type_hidden" value="<?php echo $supp['SUPPLIERTYPE']; ?>">

            <div class="mb-3">
                <label class="small fw-bold">Company Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($supp['SUPPLIERNAME']); ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Contact Person</label>
                    <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($supp['SUPPLIERCONTACT']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Phone No.</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($supp['SUPPLIERPHONE']); ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="small fw-bold">Type</label>
                <input type="text" class="form-control bg-light" value="<?php echo $supp['SUPPLIERTYPE']; ?>" readonly>
            </div>

            <?php if ($supp['SUPPLIERTYPE'] == 'LOCALFARM') { ?>
                <div class="mb-4">
                    <label class="small fw-bold">Farm Address</label>
                    <textarea name="farm_address" class="form-control" rows="2"><?php echo htmlspecialchars($supp['FARMADDRESS']); ?></textarea>
                </div>
            <?php } else { ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small fw-bold">Logistic Partner</label>
                        <input type="text" name="log_partner" class="form-control" value="<?php echo htmlspecialchars($supp['LOGISTICPARTNER']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold">Center ID</label>
                        <input type="text" name="center_id" class="form-control" value="<?php echo htmlspecialchars($supp['DISTRIBUTIONCENTERID']); ?>">
                    </div>
                </div>
            <?php } ?>

            <div class="d-flex gap-2">
                <button type="submit" name="update_supp" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">
                    Save Changes
                </button>
                <a href="supplier.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>