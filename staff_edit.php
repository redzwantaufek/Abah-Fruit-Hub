<?php
session_start();
require_once('db_conn.php');

// Security: Admin Only
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else if ($_SESSION['user_role'] != 'ADMIN') {
    echo "<script>alert('Akses Ditolak!'); window.location='staff_dashboard.php';</script>";
    exit();
}

include('includes/header.php');

// 1. Dapatkan Data Lama
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM STAFFS WHERE StaffId = :id";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    
    if(!$row) {
        echo "<script>alert('Staff tidak dijumpai!'); window.location='staff.php';</script>";
        exit();
    }
}

// 2. Proses Update
if (isset($_POST['update'])) {
    $sid = $_POST['sid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $salary = $_POST['salary'];
    $addr = $_POST['address'];

    $sql_upd = "UPDATE STAFFS SET StaffName=:nm, StaffEmail=:em, StaffPhone=:ph, StaffRole=:rl, StaffSalary=:sal, StaffAddress=:ad 
                WHERE StaffId=:sid";
    
    $stmt = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":rl", $role);
    oci_bind_by_name($stmt, ":sal", $salary);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":sid", $sid);

    if (oci_execute($stmt)) {
        echo "
        <script>
            Swal.fire({
                title: 'Berjaya!',
                text: 'Maklumat staff dikemaskini.',
                icon: 'success'
            }).then(() => {
                window.location = 'staff.php';
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
        <h3 class="mb-3 text-primary fw-bold">✏️ Kemaskini Staff</h3>
        
        <form method="POST">
            <input type="hidden" name="sid" value="<?php echo $row['STAFFID']; ?>">
            
            <div class="mb-3">
                <label>Nama Penuh</label>
                <input type="text" name="name" class="form-control" value="<?php echo $row['STAFFNAME']; ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>Emel</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $row['STAFFEMAIL']; ?>" required>
                </div>
                <div class="col">
                    <label>No. Telefon</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $row['STAFFPHONE']; ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>Jawatan</label>
                    <select name="role" class="form-select">
                        <option value="STAFF" <?php if($row['STAFFROLE']=='STAFF') echo 'selected'; ?>>Staff Biasa</option>
                        <option value="ADMIN" <?php if($row['STAFFROLE']=='ADMIN') echo 'selected'; ?>>Admin/Manager</option>
                    </select>
                </div>
                <div class="col">
                    <label>Gaji (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo $row['STAFFSALARY']; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Alamat</label>
                <textarea name="address" class="form-control" rows="2"><?php echo $row['STAFFADDRESS']; ?></textarea>
            </div>

            <button type="submit" name="update" class="btn btn-primary w-100 fw-bold">Simpan Perubahan</button>
            <a href="staff.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </form>
    </div>
</div>
</body>
</html>