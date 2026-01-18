<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];
    $type = $_POST['type'];
    
    // Additional fields based on type
    $farm_addr = $_POST['farm_address'] ?? '';
    $log_partner = $_POST['log_partner'] ?? '';
    $center_id = $_POST['center_id'] ?? '';

    // 1. Insert into SUPPLIER Parent Table
    $new_id = 0;
    $sql = "INSERT INTO SUPPLIER (SupplierId, SupplierName, SupplierContact, SupplierPhone, SupplierType) 
            VALUES (supplier_id_seq.NEXTVAL, :nm, :con, :ph, :typ) RETURNING SupplierId INTO :nid";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":con", $contact);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":typ", $type);
    oci_bind_by_name($stmt, ":nid", $new_id, -1, SQLT_INT);

    if (oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
        $success = true;

        // 2. Insert into Child Table (LocalFarm OR Distributor)
        if ($type == 'LOCALFARM') {
            $sql2 = "INSERT INTO LOCALFARM (LocalFarmId, SupplierId, FarmAddress) VALUES (farm_id_seq.NEXTVAL, :sid, :addr)";
            $stmt2 = oci_parse($dbconn, $sql2);
            oci_bind_by_name($stmt2, ":sid", $new_id);
            oci_bind_by_name($stmt2, ":addr", $farm_addr);
            if (!oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) $success = false;
        } else {
            $sql2 = "INSERT INTO DISTRIBUTOR (DistributorId, SupplierId, LogisticPartner, DistributionCenterId) VALUES (dist_id_seq.NEXTVAL, :sid, :lp, :dc)";
            $stmt2 = oci_parse($dbconn, $sql2);
            oci_bind_by_name($stmt2, ":sid", $new_id);
            oci_bind_by_name($stmt2, ":lp", $log_partner);
            oci_bind_by_name($stmt2, ":dc", $center_id);
            if (!oci_execute($stmt2, OCI_NO_AUTO_COMMIT)) $success = false;
        }

        if ($success) {
            oci_commit($dbconn);
            echo "<script>Swal.fire('Success', 'Supplier registered successfully.', 'success').then(() => { window.location = 'supplier.php'; });</script>";
        } else {
            oci_rollback($dbconn);
            echo "<script>Swal.fire('Error', 'Failed to save extra details.', 'error');</script>";
        }
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h3 class="fw-bold text-primary mb-4"><i class="fas fa-truck-loading me-2"></i>Register Supplier</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label class="fw-bold">Company Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Enter Company Name">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Contact Person</label>
                    <input type="text" name="contact" class="form-control" required placeholder="Person in charge">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="01x-xxxxxxx">
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Supplier Type</label>
                <select name="type" id="suppType" class="form-select" onchange="toggleFields()">
                    <option value="LOCALFARM">Local Farm</option>
                    <option value="DISTRIBUTOR">Distributor</option>
                </select>
            </div>

            <div id="farmFields" class="mb-4">
                <label class="fw-bold">Farm Address</label>
                <textarea name="farm_address" class="form-control" rows="2" placeholder="Enter farm location..."></textarea>
            </div>

            <div id="distFields" class="mb-4 d-none">
                <div class="row">
                    <div class="col-md-6">
                        <label class="fw-bold">Logistic Partner</label>
                        <input type="text" name="log_partner" class="form-control" placeholder="e.g. J&T, DHL">
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold">Distribution Center ID</label>
                        <input type="text" name="center_id" class="form-control" placeholder="e.g. DC-01">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-success fw-bold shadow flex-grow-1">
                    <i class="fas fa-save me-2"></i> Save Supplier
                </button>
                <a href="supplier.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFields() {
    var type = document.getElementById("suppType").value;
    if (type === "LOCALFARM") {
        document.getElementById("farmFields").classList.remove("d-none");
        document.getElementById("distFields").classList.add("d-none");
    } else {
        document.getElementById("farmFields").classList.add("d-none");
        document.getElementById("distFields").classList.remove("d-none");
    }
}
</script>
</body>
</html>