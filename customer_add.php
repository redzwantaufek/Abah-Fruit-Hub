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

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];

    $sql = "INSERT INTO CUSTOMER (CustId, CustName, CustPhone, CustEmail, CustAddress) 
            VALUES (cust_id_seq.NEXTVAL, :nm, :ph, :em, :ad)";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":ad", $addr);

    if (oci_execute($stmt)) {
        // --- SWEETALERT SUCCESS ---
        echo "
        <script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Pelanggan baru berjaya direkodkan.',
                icon: 'success',
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location = 'customer.php';
            });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Ralat', 'Gagal: " . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="card card-custom p-4 mx-auto" style="max-width: 600px;">
        <h3 class="mb-3">Daftar Pelanggan Baru</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Nama Penuh</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col">
                    <label>No. Telefon</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col">
                    <label>Emel</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label>Alamat</label>
                <textarea name="address" class="form-control" rows="2" required></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-success w-100">Simpan Data</button>
            <a href="customer.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </form>
    </div>
</div>