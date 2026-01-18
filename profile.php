<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

$sid = $_SESSION['user_id'];

if (isset($_POST['update_info'])) {
    $name = $_POST['name']; $phone = $_POST['phone']; $addr = $_POST['address'];
    $sql_update = "UPDATE STAFFS SET StaffName = :nm, StaffPhone = :ph, StaffAddress = :ad WHERE StaffId = :sid";
    $stmt = oci_parse($dbconn, $sql_update);
    oci_bind_by_name($stmt, ":nm", $name); oci_bind_by_name($stmt, ":ph", $phone); oci_bind_by_name($stmt, ":ad", $addr); oci_bind_by_name($stmt, ":sid", $sid);

    if (oci_execute($stmt)) {
        $_SESSION['user_name'] = $name;
        echo "<script>Swal.fire({ title: 'Success!', text: 'Personal info updated.', icon: 'success' });</script>";
    } else { $e = oci_error($stmt); echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>"; }
}

if (isset($_POST['change_pass'])) {
    $old_pass = $_POST['old_pass']; $new_pass = $_POST['new_pass']; $confirm_pass = $_POST['confirm_pass'];
    
    $sql_check = "SELECT StaffPassword FROM STAFFS WHERE StaffId = :sid";
    $stmt = oci_parse($dbconn, $sql_check); oci_bind_by_name($stmt, ":sid", $sid); oci_execute($stmt);
    $row_pass = oci_fetch_array($stmt, OCI_ASSOC);

    if ($row_pass['STAFFPASSWORD'] != $old_pass) { echo "<script>Swal.fire('Error', 'Incorrect old password!', 'error');</script>"; }
    else if ($new_pass != $confirm_pass) { echo "<script>Swal.fire('Error', 'New passwords do not match!', 'error');</script>"; }
    else {
        $sql_upd = "UPDATE STAFFS SET StaffPassword = :np WHERE StaffId = :sid";
        $stmt2 = oci_parse($dbconn, $sql_upd);
        oci_bind_by_name($stmt2, ":np", $new_pass); oci_bind_by_name($stmt2, ":sid", $sid);
        if (oci_execute($stmt2)) { echo "<script>Swal.fire('Success!', 'Password changed. Please login again.', 'success').then(() => { window.location = 'logout.php'; });</script>"; }
    }
}

$stmt_get = oci_parse($dbconn, "SELECT * FROM STAFFS WHERE StaffId = :sid"); oci_bind_by_name($stmt_get, ":sid", $sid); oci_execute($stmt_get);
$my_data = oci_fetch_array($stmt_get, OCI_ASSOC);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="glass-card">
                <h4 class="mb-4 text-primary fw-bold"><i class="fas fa-id-card me-2"></i> Personal Information</h4>
                <form method="POST">
                    <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" value="<?php echo $my_data['STAFFNAME']; ?>" required></div>
                    <div class="row mb-3">
                        <div class="col"><label>Email (Login ID)</label><input type="text" class="form-control bg-light" value="<?php echo $my_data['STAFFEMAIL']; ?>" readonly></div>
                        <div class="col"><label>Phone No.</label><input type="text" name="phone" class="form-control" value="<?php echo $my_data['STAFFPHONE']; ?>" required></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col"><label>Role</label><input type="text" class="form-control bg-light" value="<?php echo $my_data['STAFFROLE']; ?>" readonly></div>
                        <div class="col"><label>Current Salary</label><input type="text" class="form-control bg-light" value="RM <?php echo number_format($my_data['STAFFSALARY'], 2); ?>" readonly></div>
                    </div>
                    <div class="mb-3"><label>Address</label><textarea name="address" class="form-control" rows="2"><?php echo $my_data['STAFFADDRESS']; ?></textarea></div>
                    <button type="submit" name="update_info" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="glass-card">
                <h4 class="mb-4 text-danger fw-bold"><i class="fas fa-lock me-2"></i> Change Password</h4>
                <form method="POST">
                    <div class="mb-3"><label>Old Password</label><input type="password" name="old_pass" class="form-control" required></div>
                    <div class="mb-3"><label>New Password</label><input type="password" name="new_pass" class="form-control" minlength="6" required></div>
                    <div class="mb-3"><label>Confirm New Password</label><input type="password" name="confirm_pass" class="form-control" required></div>
                    <button type="submit" name="change_pass" class="btn btn-danger w-100"><i class="fas fa-key me-2"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>