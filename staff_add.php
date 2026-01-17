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
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];
    $role = $_POST['role'];
    $salary = $_POST['salary'];

    $sql = "INSERT INTO STAFFS (StaffId, StaffName, StaffEmail, StaffPassword, StaffPhone, StaffAddress, StaffRole, StaffSalary) 
            VALUES (staff_id_seq.NEXTVAL, :nm, :em, :pw, :ph, :ad, :rl, :sal)";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":pw", $pass);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":rl", $role);
    oci_bind_by_name($stmt, ":sal", $salary);

    if (oci_execute($stmt)) {
        // --- SWEETALERT SUCCESS ---
        echo "
        <script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Staff baru berjaya didaftarkan.',
                icon: 'success',
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location = 'staff.php';
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
        <h3 class="mb-3">Daftar Pekerja Baru</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Nama Penuh</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col">
                    <label>Emel (Login ID)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col">
                    <label>Kata Laluan</label>
                    <input type="text" name="password" class="form-control" maxlength="15" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <label>Jawatan</label>
                    <select name="role" class="form-select">
                        <option value="STAFF">Staff Biasa</option>
                        <option value="ADMIN">Manager/Admin</option>
                    </select>
                </div>
                <div class="col">
                    <label>Gaji (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" required>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label>No. Telefon</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100">Simpan Staff</button>
            <a href="staff.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </form>
    </div>
</div>