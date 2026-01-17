<?php
session_start();
require_once('db_conn.php');

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php');

$sid = $_SESSION['user_id'];

// --- 1. PROSES KEMASKINI MAKLUMAT (INFO) ---
if (isset($_POST['update_info'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];

    // Kita update Nama, Phone, Alamat sahaja. Gaji & Role TIDAK DIUSIK.
    $sql_update = "UPDATE STAFFS SET StaffName = :nm, StaffPhone = :ph, StaffAddress = :ad WHERE StaffId = :sid";
    $stmt = oci_parse($dbconn, $sql_update);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":sid", $sid);

    if (oci_execute($stmt)) {
        // Update session name juga supaya nama di dashboard bertukar terus
        $_SESSION['user_name'] = $name;
        
        echo "<script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Maklumat peribadi telah dikemaskini.',
                icon: 'success',
                confirmButtonColor: '#198754'
            });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Ralat', '" . $e['message'] . "', 'error');</script>";
    }
}

// --- 2. PROSES TUKAR PASSWORD ---
if (isset($_POST['change_pass'])) {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Semak password lama
    $sql_check = "SELECT StaffPassword FROM STAFFS WHERE StaffId = :sid";
    $stmt = oci_parse($dbconn, $sql_check);
    oci_bind_by_name($stmt, ":sid", $sid);
    oci_execute($stmt);
    $row_pass = oci_fetch_array($stmt, OCI_ASSOC);

    if ($row_pass['STAFFPASSWORD'] != $old_pass) {
        echo "<script>Swal.fire('Ralat', 'Kata laluan lama salah!', 'error');</script>";
    } else if ($new_pass != $confirm_pass) {
        echo "<script>Swal.fire('Ralat', 'Kata laluan baru tidak sama!', 'error');</script>";
    } else {
        // Update password baru
        $sql_upd = "UPDATE STAFFS SET StaffPassword = :np WHERE StaffId = :sid";
        $stmt2 = oci_parse($dbconn, $sql_upd);
        oci_bind_by_name($stmt2, ":np", $new_pass);
        oci_bind_by_name($stmt2, ":sid", $sid);
        
        if (oci_execute($stmt2)) {
            echo "<script>
            Swal.fire('Berjaya!', 'Kata laluan ditukar. Sila login semula.', 'success').then(() => {
                window.location = 'logout.php';
            });
            </script>";
        }
    }
}

// --- 3. DAPATKAN DATA TERKINI USER ---
$sql_get = "SELECT * FROM STAFFS WHERE StaffId = :sid";
$stmt_get = oci_parse($dbconn, $sql_get);
oci_bind_by_name($stmt_get, ":sid", $sid);
oci_execute($stmt_get);
$my_data = oci_fetch_array($stmt_get, OCI_ASSOC);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="glass-card">
                <h4 class="mb-4 text-primary fw-bold"><i class="fas fa-id-card me-2"></i> Maklumat Peribadi</h4>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Nama Penuh</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $my_data['STAFFNAME']; ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label>Emel (ID Login)</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $my_data['STAFFEMAIL']; ?>" readonly>
                            <small class="text-muted">Emel tidak boleh diubah.</small>
                        </div>
                        <div class="col">
                            <label>No. Telefon</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $my_data['STAFFPHONE']; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label>Jawatan</label>
                            <input type="text" class="form-control bg-light" value="<?php echo $my_data['STAFFROLE']; ?>" readonly>
                        </div>
                        <div class="col">
                            <label>Gaji Semasa</label>
                            <input type="text" class="form-control bg-light" value="RM <?php echo number_format($my_data['STAFFSALARY'], 2); ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Alamat Rumah</label>
                        <textarea name="address" class="form-control" rows="2"><?php echo $my_data['STAFFADDRESS']; ?></textarea>
                    </div>

                    <button type="submit" name="update_info" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i> Simpan Maklumat
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="glass-card">
                <h4 class="mb-4 text-danger fw-bold"><i class="fas fa-lock me-2"></i> Tukar Kata Laluan</h4>
                
                <form method="POST">
                    <div class="mb-3">
                        <label>Kata Laluan Lama</label>
                        <input type="password" name="old_pass" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Kata Laluan Baru</label>
                        <input type="password" name="new_pass" class="form-control" minlength="6" required>
                    </div>

                    <div class="mb-3">
                        <label>Ulang Kata Laluan Baru</label>
                        <input type="password" name="confirm_pass" class="form-control" required>
                    </div>

                    <button type="submit" name="change_pass" class="btn btn-danger w-100">
                        <i class="fas fa-key me-2"></i> Tukar Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>