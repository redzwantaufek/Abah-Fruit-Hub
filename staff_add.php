<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $pass = $_POST['password'];
    $role = $_POST['role'];
    $manager = $_POST['manager_id'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $address = $_POST['address'];
    $email = $_POST['email'];

    if(empty($manager)) { $manager = null; }

    $sql = "INSERT INTO STAFFS (StaffId, StaffName, StaffPassword, StaffRole, ManagerId, StaffPhone, StaffSalary, StaffAddress, StaffEmail) 
            VALUES (staff_id_seq.NEXTVAL, :nm, :pw, :rl, :mgr, :ph, :sal, :ad, :em)";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":pw", $pass);
    oci_bind_by_name($stmt, ":rl", $role);
    oci_bind_by_name($stmt, ":mgr", $manager);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":sal", $salary);
    oci_bind_by_name($stmt, ":ad", $address);
    oci_bind_by_name($stmt, ":em", $email);

    if (oci_execute($stmt)) {
        echo "<script>Swal.fire('Success', 'New staff registered successfully.', 'success').then(() => { window.location = 'staff.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-5" style="max-width: 700px;">
        <h3 class="fw-bold text-primary mb-4"><i class="fas fa-user-plus me-2"></i>Register New Staff</h3>
        
        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Staff Full Name">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Email (Login ID)</label>
                    <input type="email" name="email" class="form-control" required placeholder="email@fruit.com">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Create password">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Role</label>
                    <select name="role" class="form-select">
                        <option value="STAFF">Staff</option>
                        <option value="ADMIN">Admin</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required placeholder="01x-xxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Salary (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" required placeholder="0.00">
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Manager</label>
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
                <label class="fw-bold">Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="Home address"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-primary fw-bold shadow flex-grow-1">
                    <i class="fas fa-save me-2"></i> Register Staff
                </button>
                <a href="staff.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>