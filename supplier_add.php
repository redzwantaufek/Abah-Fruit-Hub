<?php
session_start();
require_once('db_conn.php');

// --- SECURITY CHECK: ADMIN ONLY ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else if ($_SESSION['user_role'] != 'ADMIN') {
    echo "<script>alert('Akses Ditolak!'); window.location='staff_dashboard.php';</script>";
    exit();
}
// ----------------------------------

include('includes/header.php');

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $type = $_POST['type'];

    $farm_addr = $_POST['farm_address'];
    $license = $_POST['license'];
    $center = $_POST['center_id'];
    $logistic = $_POST['logistic'];

    // 1. Ambil ID dari Sequence (UPDATED to match your SQL: supp_id_seq)
    $sql_seq = "SELECT supp_id_seq.NEXTVAL AS NEXT_ID FROM DUAL";
    $s_seq = oci_parse($dbconn, $sql_seq);
    
    if (!oci_execute($s_seq)) {
        $e = oci_error($s_seq);
        echo "<script>Swal.fire('Database Error', 'Sequence supp_id_seq missing! Run your SQL command.', 'error');</script>";
        exit();
    }

    $r_seq = oci_fetch_array($s_seq, OCI_ASSOC);
    $new_id = $r_seq['NEXT_ID'];

    if (!$new_id) {
        echo "<script>Swal.fire('Error', 'Failed to generate Supplier ID', 'error');</script>";
        exit();
    }

    // 2. Insert Parent (SUPPLIER)
    $sql_main = "INSERT INTO SUPPLIER (SupplierId, SupplierName, SupplierContact, SupplierPhone, SupplierEmail, SupplierType) 
                 VALUES (:id, :nm, :con, :ph, :em, :typ)";
    $stmt1 = oci_parse($dbconn, $sql_main);
    oci_bind_by_name($stmt1, ":id", $new_id);
    oci_bind_by_name($stmt1, ":nm", $name);
    oci_bind_by_name($stmt1, ":con", $contact);
    oci_bind_by_name($stmt1, ":ph", $phone);
    oci_bind_by_name($stmt1, ":em", $email);
    oci_bind_by_name($stmt1, ":typ", $type);
    
    $execute_main = oci_execute($stmt1, OCI_NO_AUTO_COMMIT);

    // 3. Insert Child (LOCALFARM or DISTRIBUTOR)
    $execute_child = false;
    
    if ($type == 'LOCALFARM') {
        $sql_child = "INSERT INTO LOCALFARM (SupplierId, FarmAddress) VALUES (:id, :faddr)";
        $stmt2 = oci_parse($dbconn, $sql_child);
        oci_bind_by_name($stmt2, ":id", $new_id);
        oci_bind_by_name($stmt2, ":faddr", $farm_addr);
        $execute_child = oci_execute($stmt2, OCI_NO_AUTO_COMMIT);
        
    } else if ($type == 'DISTRIBUTOR') {
        $sql_child = "INSERT INTO DISTRIBUTOR (SupplierId, BusinessLicenseNo, DistributionCenterId, LogisticPartner) 
                      VALUES (:id, :lic, :dc, :log)";
        $stmt2 = oci_parse($dbconn, $sql_child);
        oci_bind_by_name($stmt2, ":id", $new_id);
        oci_bind_by_name($stmt2, ":lic", $license);
        oci_bind_by_name($stmt2, ":dc", $center);
        oci_bind_by_name($stmt2, ":log", $logistic);
        $execute_child = oci_execute($stmt2, OCI_NO_AUTO_COMMIT);
    }

    // 4. Commit or Rollback
    if ($execute_main && $execute_child) {
        oci_commit($dbconn);
        echo "
        <script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Supplier baru berjaya ditambah (ID: $new_id).',
                icon: 'success',
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location = 'supplier.php';
            });
        </script>";
    } else {
        oci_rollback($dbconn);
        $e = oci_error($stmt1); // Check parent error
        if (!$e && isset($stmt2)) $e = oci_error($stmt2); // Check child error
        echo "<script>Swal.fire('Ralat', 'Gagal menyimpan data: " . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="card card-custom p-4 mx-auto" style="max-width: 700px;">
        <h3 class="mb-3">Daftar Supplier Baru</h3>
        <form method="POST">
            <h5 class="text-primary">Maklumat Umum</h5>
            <div class="row mb-2">
                <div class="col"><label>Nama Syarikat</label><input type="text" name="name" class="form-control" required></div>
                <div class="col"><label>Contact Person</label><input type="text" name="contact" class="form-control" required></div>
            </div>
            <div class="row mb-3">
                <div class="col"><label>No. Telefon</label><input type="text" name="phone" class="form-control" required></div>
                <div class="col"><label>Emel</label><input type="email" name="email" class="form-control" required></div>
            </div>

            <div class="mb-3">
                <label>Jenis Supplier</label>
                <select name="type" id="suppType" class="form-select" onchange="toggleForm()">
                    <option value="LOCALFARM">Ladang Tempatan (Local Farm)</option>
                    <option value="DISTRIBUTOR">Pengedar Besar (Distributor)</option>
                </select>
            </div>

            <div id="formFarm" class="bg-light p-3 border rounded mb-3">
                <h6 class="text-success">Info Ladang</h6>
                <label>Alamat Ladang</label>
                <input type="text" name="farm_address" class="form-control">
            </div>

            <div id="formDist" class="bg-light p-3 border rounded mb-3" style="display:none;">
                <h6 class="text-info">Info Distributor</h6>
                <div class="row">
                    <div class="col"><label>No. Lesen</label><input type="text" name="license" class="form-control"></div>
                    <div class="col"><label>ID Pusat Edaran</label><input type="text" name="center_id" class="form-control"></div>
                </div>
                <div class="mt-2">
                    <label>Rakan Logistik (Lalamove/J&T)</label>
                    <input type="text" name="logistic" class="form-control">
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-warning w-100">Simpan Supplier</button>
        </form>
    </div>
</div>

<script>
function toggleForm() {
    var type = document.getElementById("suppType").value;
    if(type === "LOCALFARM") {
        document.getElementById("formFarm").style.display = "block";
        document.getElementById("formDist").style.display = "none";
    } else {
        document.getElementById("formFarm").style.display = "none";
        document.getElementById("formDist").style.display = "block";
    }
}
</script>