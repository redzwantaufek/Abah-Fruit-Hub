<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php');

// 1. Dapatkan Data Lama
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM CUSTOMER WHERE CustId = :id";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    
    if(!$row) {
        echo "<script>alert('Pelanggan tidak dijumpai!'); window.location='customer.php';</script>";
        exit();
    }
}

// 2. Proses Update
if (isset($_POST['update'])) {
    $cid = $_POST['cid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];

    $sql_upd = "UPDATE CUSTOMER SET CustName=:nm, CustEmail=:em, CustPhone=:ph, CustAddress=:ad 
                WHERE CustId=:cid";
    
    $stmt = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":cid", $cid);

    if (oci_execute($stmt)) {
        echo "
        <script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Info pelanggan dikemaskini.',
                icon: 'success'
            }).then(() => {
                window.location = 'customer.php';
            });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Ralat', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-3 text-success fw-bold">✏️ Kemaskini Pelanggan</h3>
        
        <form method="POST">
            <input type="hidden" name="cid" value="<?php echo $row['CUSTID']; ?>">
            
            <div class="mb-3">
                <label>Nama Pelanggan</label>
                <input type="text" name="name" class="form-control" value="<?php echo $row['CUSTNAME']; ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>No. Telefon</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $row['CUSTPHONE']; ?>" required>
                </div>
                <div class="col">
                    <label>Emel</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $row['CUSTEMAIL']; ?>">
                </div>
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="address" class="form-control" rows="2"><?php echo $row['CUSTADDRESS']; ?></textarea>
            </div>

            <button type="submit" name="update" class="btn btn-success w-100 fw-bold">Simpan Perubahan</button>
            <a href="customer.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </form>
    </div>
</div>
</body>
</html>