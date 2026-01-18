<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

$msg = "";

if (isset($_POST['save_supp'])) {
    $type = $_POST['type'];
    $new_id = 0;

    // 1. Insert Parent (SUPPLIER) - Guna supp_id_seq
    // Schema anda wajibkan: SupplierName, SupplierPhone, SupplierEmail, SupplierType
    $q1 = "INSERT INTO SUPPLIER (SupplierId, SupplierName, SupplierContact, SupplierPhone, SupplierEmail, SupplierType) 
           VALUES (supp_id_seq.NEXTVAL, :nm, :con, :ph, :em, :typ) RETURNING SupplierId INTO :nid";
    
    $stmt = oci_parse($dbconn, $q1);
    oci_bind_by_name($stmt, ":nm", $_POST['name']);
    oci_bind_by_name($stmt, ":con", $_POST['contact']);
    oci_bind_by_name($stmt, ":ph", $_POST['phone']);
    oci_bind_by_name($stmt, ":em", $_POST['email']);
    oci_bind_by_name($stmt, ":typ", $type);
    oci_bind_by_name($stmt, ":nid", $new_id, -1, SQLT_INT);

    if (oci_execute($stmt, OCI_NO_AUTO_COMMIT)) {
        $success = true;

        // 2. Insert Child
        if ($type == 'LOCALFARM') {
            // Table LOCALFARM: FarmAddress (NOT NULL)
            $q2 = "INSERT INTO LOCALFARM (SupplierId, FarmAddress) VALUES (:sid, :addr)";
            $s2 = oci_parse($dbconn, $q2);
            oci_bind_by_name($s2, ":sid", $new_id);
            oci_bind_by_name($s2, ":addr", $_POST['farm_address']);
            if (!oci_execute($s2, OCI_NO_AUTO_COMMIT)) $success = false;
        } else {
            // Table DISTRIBUTOR: BusinessLicenseNo (NOT NULL)
            $q2 = "INSERT INTO DISTRIBUTOR (SupplierId, BusinessLicenseNo, DistributionCenterId, LogisticPartner) 
                   VALUES (:sid, :bl, :dc, :lp)";
            $s2 = oci_parse($dbconn, $q2);
            oci_bind_by_name($s2, ":sid", $new_id);
            oci_bind_by_name($s2, ":bl", $_POST['license_no']); // Wajib
            oci_bind_by_name($s2, ":dc", $_POST['center_id']);
            oci_bind_by_name($s2, ":lp", $_POST['log_partner']);
            if (!oci_execute($s2, OCI_NO_AUTO_COMMIT)) $success = false;
        }

        if ($success) {
            oci_commit($dbconn);
            $msg = "Swal.fire('Success', 'Supplier registered.', 'success').then(() => { window.location = 'supplier.php'; });";
        } else {
            oci_rollback($dbconn);
            $e = oci_error($s2);
            $msg = "Swal.fire('Error', 'Failed to save child info: " . $e['message'] . "', 'error');";
        }
    } else {
        $e = oci_error($stmt);
        $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
    }
}
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 600px;">
        <h4 class="fw-bold text-primary mb-4"><i class="fas fa-truck-loading me-2"></i>Register Supplier</h4>
        
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold">Company Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Contact Person</label>
                    <input type="text" name="contact" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Phone No.</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="small fw-bold">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="small fw-bold">Type</label>
                <select name="type" id="suppType" class="form-select" onchange="toggleFields()">
                    <option value="LOCALFARM">Local Farm</option>
                    <option value="DISTRIBUTOR">Distributor</option>
                </select>
            </div>

            <div id="farmFields" class="mb-4">
                <label class="small fw-bold">Farm Address (Required)</label>
                <textarea name="farm_address" class="form-control" rows="2"></textarea>
            </div>

            <div id="distFields" class="mb-4 d-none">
                <div class="mb-2">
                    <label class="small fw-bold">Business License No (Required)</label>
                    <input type="text" name="license_no" class="form-control">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="small fw-bold">Logistic Partner</label>
                        <input type="text" name="log_partner" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="small fw-bold">Distribution Center ID</label>
                        <input type="text" name="center_id" class="form-control">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="save_supp" class="btn btn-success fw-bold flex-grow-1 shadow-sm">
                    Save Supplier
                </button>
                <a href="supplier.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFields() {
    var t = document.getElementById("suppType").value;
    document.getElementById("farmFields").classList.toggle("d-none", t !== "LOCALFARM");
    document.getElementById("distFields").classList.toggle("d-none", t === "LOCALFARM");
    
    // Set required attributes based on type to prevent HTML5 validation error
    if(t === "LOCALFARM") {
        document.querySelector('[name="farm_address"]').required = true;
        document.querySelector('[name="license_no"]').required = false;
    } else {
        document.querySelector('[name="farm_address"]').required = false;
        document.querySelector('[name="license_no"]').required = true;
    }
}
// Init
toggleFields();
</script>
</body>
</html>