<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

$staff_id = $_SESSION['user_id'];

// Get User Data
$sql = "SELECT * FROM STAFFS WHERE StaffId = :sid";
$stmt = oci_parse($dbconn, $sql);
oci_bind_by_name($stmt, ":sid", $staff_id);
oci_execute($stmt);
$user = oci_fetch_array($stmt, OCI_ASSOC);

// Handle Change Password
if (isset($_POST['change_pass'])) {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($old_pass != $user['STAFFPASSWORD']) {
        echo "<script>Swal.fire('Error', 'Old password is incorrect.', 'error');</script>";
    } elseif ($new_pass != $confirm_pass) {
        echo "<script>Swal.fire('Error', 'New passwords do not match.', 'error');</script>";
    } else {
        $sql_upd = "UPDATE STAFFS SET StaffPassword = :np WHERE StaffId = :sid";
        $stmt_upd = oci_parse($dbconn, $sql_upd);
        oci_bind_by_name($stmt_upd, ":np", $new_pass);
        oci_bind_by_name($stmt_upd, ":sid", $staff_id);
        
        if (oci_execute($stmt_upd)) {
            echo "<script>Swal.fire('Success', 'Password updated successfully!', 'success');</script>";
        } else {
            echo "<script>Swal.fire('Error', 'Failed to update database.', 'error');</script>";
        }
    }
}
?>

<div class="container-fluid">
    <h2 class="fw-bold text-white text-shadow mb-4"><i class="fas fa-user-circle me-2"></i>My Profile</h2>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="glass-card p-4 h-100">
                <h5 class="text-primary fw-bold mb-3 border-bottom pb-2"><i class="fas fa-id-card me-2"></i>Personal Information</h5>
                
                <div class="mb-3">
                    <label class="small text-muted fw-bold">Full Name</label>
                    <input type="text" class="form-control bg-light" value="<?php echo $user['STAFFNAME']; ?>" readonly>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="small text-muted fw-bold">Email (Login ID)</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $user['STAFFEMAIL']; ?>" readonly>
                    </div>
                    <div class="col">
                        <label class="small text-muted fw-bold">Phone No.</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $user['STAFFPHONE']; ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="small text-muted fw-bold">Role</label>
                        <input type="text" class="form-control bg-light" value="<?php echo $user['STAFFROLE']; ?>" readonly>
                    </div>
                    <div class="col">
                        <label class="small text-muted fw-bold">Current Salary</label>
                        <input type="text" class="form-control bg-light" value="RM <?php echo number_format($user['STAFFSALARY'], 2); ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="small text-muted fw-bold">Address</label>
                    <textarea class="form-control bg-light" rows="2" readonly><?php echo $user['STAFFADDRESS']; ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="glass-card p-4 h-100">
                <h5 class="text-danger fw-bold mb-3 border-bottom pb-2"><i class="fas fa-lock me-2"></i>Change Password</h5>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="fw-bold">Old Password</label>
                        <input type="password" name="old_pass" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">New Password</label>
                        <input type="password" name="new_pass" class="form-control" maxlength="15" required>
                    </div>

                    <div class="mb-4">
                        <label class="fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_pass" class="form-control" maxlength="15" required>
                    </div>

                    <button type="submit" name="change_pass" class="btn btn-danger w-100 fw-bold shadow">
                        <i class="fas fa-key me-2"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>