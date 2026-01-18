<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

$msg = "";

if (isset($_POST['save_staff'])) {
    $mgr = empty($_POST['manager_id']) ? null : $_POST['manager_id'];

    $q = "INSERT INTO STAFFS (StaffId, StaffName, StaffPassword, StaffRole, ManagerId, StaffPhone, StaffSalary, StaffAddress, StaffEmail) 
          VALUES (staff_id_seq.NEXTVAL, :nm, :pw, :rl, :mgr, :ph, :sal, :ad, :em)";
    
    $stmt = oci_parse($dbconn, $q);
    oci_bind_by_name($stmt, ":nm", $_POST['name']);
    oci_bind_by_name($stmt, ":pw", $_POST['password']);
    oci_bind_by_name($stmt, ":rl", $_POST['role']);
    oci_bind_by_name($stmt, ":mgr", $mgr);
    oci_bind_by_name($stmt, ":ph", $_POST['phone']);
    oci_bind_by_name($stmt, ":sal", $_POST['salary']);
    oci_bind_by_name($stmt, ":ad", $_POST['address']);
    oci_bind_by_name($stmt, ":em", $_POST['email']);

    if (oci_execute($stmt)) {
        $msg = "Swal.fire('Success', 'Staff registered.', 'success').then(() => { window.location = 'staff.php'; });";
    } else {
        $e = oci_error($stmt);
        $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
    }
}
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 700px;">
        <h4 class="fw-bold text-primary mb-4"><i class="fas fa-user-plus me-2"></i>New Staff Registration</h4>
        
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Email (Login ID)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Role</label>
                    <select name="role" class="form-select">
                        <option value="STAFF">Staff</option>
                        <option value="ADMIN">Admin</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Phone No.</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Salary (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="small fw-bold">Manager</label>
                <select name="manager_id" class="form-select">
                    <option value="">-- No Manager --</option>
                    <?php
                    $s = oci_parse($dbconn, "SELECT StaffId, StaffName FROM STAFFS ORDER BY StaffName");
                    oci_execute($s);
                    while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                        echo "<option value='".$r['STAFFID']."'>".$r['STAFFNAME']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="small fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="save_staff" class="btn btn-primary fw-bold w-100 shadow-sm">
                    Register Staff
                </button>
                <a href="staff.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>