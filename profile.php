<?php
session_start();
require_once('db_conn.php');

// Check login
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

include('includes/header.php');

$uid = $_SESSION['user_id'];
$msg = ""; // Simpan message alert

// Update Personal Details
if (isset($_POST['save_profile'])) {
    $phone = $_POST['phone'];
    $addr  = $_POST['address'];

    $q = "UPDATE STAFFS SET StaffPhone = :ph, StaffAddress = :ad WHERE StaffId = :id";
    $stmt = oci_parse($dbconn, $q);
    
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":id", $uid);

    if (oci_execute($stmt)) {
        $msg = "Swal.fire('Success', 'Profile updated successfully.', 'success');";
    } else {
        $msg = "Swal.fire('Error', 'Update failed.', 'error');";
    }
}

// Change Password Logic
if (isset($_POST['save_pass'])) {
    $curr_pass = $_POST['curr_pass'];
    $new_pass  = $_POST['new_pass'];
    $cfm_pass  = $_POST['cfm_pass'];

    // Verify old password
    $check = oci_parse($dbconn, "SELECT StaffPassword FROM STAFFS WHERE StaffId = :id");
    oci_bind_by_name($check, ":id", $uid);
    oci_execute($check);
    $row = oci_fetch_array($check, OCI_ASSOC);

    if ($row && $curr_pass == $row['STAFFPASSWORD']) {
        if ($new_pass === $cfm_pass) {
            // Update password
            $q_upd = "UPDATE STAFFS SET StaffPassword = :np WHERE StaffId = :id";
            $s = oci_parse($dbconn, $q_upd);
            oci_bind_by_name($s, ":np", $new_pass);
            oci_bind_by_name($s, ":id", $uid);
            
            if (oci_execute($s)) {
                $msg = "Swal.fire('Success', 'Password changed!', 'success');";
            }
        } else {
            $msg = "Swal.fire('Error', 'New passwords do not match.', 'error');";
        }
    } else {
        $msg = "Swal.fire('Error', 'Current password incorrect.', 'error');";
    }
}

// Fetch user info
$q_user = "SELECT * FROM STAFFS WHERE StaffId = :id";
$s_user = oci_parse($dbconn, $q_user);
oci_bind_by_name($s_user, ":id", $uid);
oci_execute($s_user);
$me = oci_fetch_array($s_user, OCI_ASSOC);
?>

<script><?php echo $msg; ?></script>

<div class="container-fluid">
    <h3 class="fw-bold text-white mb-4"><i class="fas fa-user-circle me-2"></i>My Profile</h3>

    <div class="row">
        <div class="col-md-7 mb-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow" style="width: 55px; height: 55px; font-size: 22px;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><?php echo $me['STAFFNAME']; ?></h5>
                        <small class="badge bg-dark"><?php echo $me['STAFFROLE']; ?></small>
                    </div>
                </div>

                <form method="POST">
                    <h6 class="text-primary fw-bold mb-3">Edit Details</h6>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">Staff ID</label>
                            <input type="text" class="form-control bg-light form-control-sm" value="<?php echo $me['STAFFID']; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">Email</label>
                            <input type="text" class="form-control bg-light form-control-sm" value="<?php echo $me['STAFFEMAIL']; ?>" readonly>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="small fw-bold">Phone No.</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo $me['STAFFPHONE']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted">Salary</label>
                            <input type="text" class="form-control bg-light" value="RM <?php echo number_format($me['STAFFSALARY'], 2); ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold">Address</label>
                        <textarea name="address" class="form-control" rows="2" required><?php echo $me['STAFFADDRESS']; ?></textarea>
                    </div>

                    <button type="submit" name="save_profile" class="btn btn-primary fw-bold w-100 shadow-sm">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-5 mb-3">
            <div class="glass-card p-4 h-100 border-top border-3 border-danger">
                <h6 class="text-danger fw-bold mb-3"><i class="fas fa-key me-2"></i>Change Password</h6>
                
                <form method="POST">
                    <div class="mb-2">
                        <label class="small fw-bold">Current Password</label>
                        <input type="password" name="curr_pass" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label class="small fw-bold">New Password</label>
                        <input type="password" name="new_pass" class="form-control" maxlength="20" required>
                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold">Confirm Password</label>
                        <input type="password" name="cfm_pass" class="form-control" maxlength="20" required>
                    </div>

                    <button type="submit" name="save_pass" class="btn btn-danger w-100 fw-bold shadow-sm">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>