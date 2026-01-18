<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

if (!isset($_GET['id'])) { header("Location: staff.php"); exit(); }
$sid = $_GET['id'];

$sql = "SELECT * FROM STAFFS WHERE StaffId = :id";
$stmt = oci_parse($dbconn, $sql);
oci_bind_by_name($stmt, ":id", $sid);
oci_execute($stmt);
$row = oci_fetch_array($stmt, OCI_ASSOC);

if (!$row) { echo "<script>window.location='staff.php';</script>"; exit(); }

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $manager = $_POST['manager_id'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    if(empty($manager)) { $manager = null; }

    $sql_upd = "UPDATE STAFFS SET StaffName=:nm, StaffRole=:rl, ManagerId=:mgr, StaffPhone=:ph, StaffSalary=:sal, StaffAddress=:ad, StaffEmail=:em WHERE StaffId=:id";
    
    $stmt_upd = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt_upd, ":nm", $name);
    oci_bind_by_name($stmt_upd, ":rl", $role);
    oci_bind_by_name($stmt_upd, ":mgr", $manager);
    oci_bind_by_name($stmt_upd, ":ph", $phone);
    oci_bind_by_name($stmt_upd, ":sal", $salary);
    oci_bind_by_name($stmt_upd, ":ad", $address);
    oci_bind_by_name($stmt_upd, ":em", $email);
    oci_bind_by_name($stmt_upd, ":id", $sid);

    if (oci_execute($stmt_upd)) {
        echo "<script>Swal.fire('Updated', 'Staff details updated.', 'success').then(() => { window.location = 'staff.php'; });</script>";
    } else {
        $e = oci_error($stmt_upd);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 700px;">
        <h3 class="fw-bold text-primary mb-4"><i class="fas fa-user-edit me-2"></i>Edit Staff Details</h3>
        
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['STAFFNAME']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Email (Login ID)</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['STAFFEMAIL']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Role</label>
                    <select name="role" class="form-select">
                        <option value="STAFF" <?php if($row['STAFFROLE']=='STAFF') echo 'selected'; ?>>Staff</option>
                        <option value="ADMIN" <?php if($row['STAFFROLE']=='ADMIN') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="fw-bold">Manager</label>
                    <select name="manager_id" class="form-select">
                        <option value="">-- No Manager --</option>
                        <?php
                        $s = oci_parse($dbconn, "SELECT StaffId, StaffName FROM STAFFS WHERE StaffId != :sid ORDER BY StaffName");
                        oci_bind_by_name($s, ":sid", $sid);
                        oci_execute($s);
                        while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                            $selected = ($r['STAFFID'] == $row['MANAGERID']) ? 'selected' : '';
                            echo "<option value='".$r['STAFFID']."' $selected>".$r['STAFFNAME']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['STAFFPHONE']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Salary (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo $row['STAFFSALARY']; ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($row['STAFFADDRESS']); ?></textarea>
            </div>

            <div class="alert alert-info small">
                <i class="fas fa-info-circle me-1"></i> Note: Password can only be changed by the staff themselves in "My Profile".
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update" class="btn btn-primary fw-bold shadow flex-grow-1">
                    <i class="fas fa-save me-2"></i> Update Changes
                </button>
                <a href="staff.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>