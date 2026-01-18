<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

$sid = $_GET['id'] ?? null;
if (!$sid) { header("Location: staff.php"); exit(); }

$msg = "";

// Update
if (isset($_POST['update_staff'])) {
    $mgr = empty($_POST['manager_id']) ? null : $_POST['manager_id'];

    $q = "UPDATE STAFFS SET StaffName=:nm, StaffRole=:rl, ManagerId=:mgr, StaffPhone=:ph, StaffSalary=:sal, StaffAddress=:ad, StaffEmail=:em WHERE StaffId=:id";
    $stmt = oci_parse($dbconn, $q);
    oci_bind_by_name($stmt, ":nm", $_POST['name']);
    oci_bind_by_name($stmt, ":rl", $_POST['role']);
    oci_bind_by_name($stmt, ":mgr", $mgr);
    oci_bind_by_name($stmt, ":ph", $_POST['phone']);
    oci_bind_by_name($stmt, ":sal", $_POST['salary']);
    oci_bind_by_name($stmt, ":ad", $_POST['address']);
    oci_bind_by_name($stmt, ":em", $_POST['email']);
    oci_bind_by_name($stmt, ":id", $sid);

    if (oci_execute($stmt)) {
        $msg = "Swal.fire('Updated', 'Staff details saved.', 'success').then(() => { window.location = 'staff.php'; });";
    } else {
        $e = oci_error($stmt);
        $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
    }
}

// Get Data
$q_get = "SELECT * FROM STAFFS WHERE StaffId = :id";
$s_get = oci_parse($dbconn, $q_get);
oci_bind_by_name($s_get, ":id", $sid);
oci_execute($s_get);
$staff = oci_fetch_array($s_get, OCI_ASSOC);
if (!$staff) die("<script>window.location='staff.php';</script>");
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 700px;">
        <h4 class="fw-bold text-primary mb-4"><i class="fas fa-user-edit me-2"></i>Edit Staff</h4>
        
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($staff['STAFFNAME']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($staff['STAFFEMAIL']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Role</label>
                    <select name="role" class="form-select">
                        <option value="STAFF" <?php echo ($staff['STAFFROLE']=='STAFF')?'selected':''; ?>>Staff</option>
                        <option value="ADMIN" <?php echo ($staff['STAFFROLE']=='ADMIN')?'selected':''; ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Manager</label>
                    <select name="manager_id" class="form-select">
                        <option value="">-- No Manager --</option>
                        <?php
                        $s = oci_parse($dbconn, "SELECT StaffId, StaffName FROM STAFFS WHERE StaffId != :sid ORDER BY StaffName");
                        oci_bind_by_name($s, ":sid", $sid);
                        oci_execute($s);
                        while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                            $sel = ($r['STAFFID'] == $staff['MANAGERID']) ? 'selected' : '';
                            echo "<option value='".$r['STAFFID']."' $sel>".$r['STAFFNAME']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Phone No.</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($staff['STAFFPHONE']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Salary (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo $staff['STAFFSALARY']; ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($staff['STAFFADDRESS']); ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update_staff" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">
                    Save Changes
                </button>
                <a href="staff.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>