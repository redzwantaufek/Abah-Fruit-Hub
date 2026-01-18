<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

include('includes/header.php');

// Pre-load Manager List (Role=ADMIN)
$sql_boss = "SELECT StaffId, StaffName FROM STAFFS WHERE StaffRole = 'ADMIN' ORDER BY StaffName ASC";
$stmt_boss = oci_parse($dbconn, $sql_boss);
oci_execute($stmt_boss);

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $phone = $_POST['phone'];
    $addr = $_POST['address'];
    $role = $_POST['role'];
    $salary = $_POST['salary'];
    
    // Handle Manager ID
    $mgr_id = $_POST['manager_id'];
    if($mgr_id == "") $mgr_id = null;

    $sql = "INSERT INTO STAFFS (StaffId, StaffName, StaffEmail, StaffPassword, StaffPhone, StaffAddress, StaffRole, StaffSalary, ManagerId) 
            VALUES (staff_id_seq.NEXTVAL, :nm, :em, :pw, :ph, :ad, :rl, :sal, :mgr)";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":pw", $pass);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":rl", $role);
    oci_bind_by_name($stmt, ":sal", $salary);
    oci_bind_by_name($stmt, ":mgr", $mgr_id);

    if (oci_execute($stmt)) {
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'New staff registered successfully.',
                icon: 'success',
                confirmButtonColor: '#198754'
            }).then(() => { window.location = 'staff.php'; });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', 'Failed: " . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-3 text-primary fw-bold">➕ Register New Staff</h3>
        <form method="POST">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col">
                    <label>Email (Login ID)</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col">
                    <label>Password</label>
                    <input type="text" name="password" class="form-control" maxlength="15" required>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col">
                    <label>Role</label>
                    <select name="role" class="form-select">
                        <option value="STAFF">Regular Staff</option>
                        <option value="ADMIN">Manager/Admin</option>
                    </select>
                </div>
                <div class="col">
                    <label>Reports To (Manager)</label>
                    <select name="manager_id" class="form-select">
                        <option value="">-- None (Is Boss) --</option>
                        <?php
                        while ($row_b = oci_fetch_array($stmt_boss, OCI_ASSOC)) {
                            echo "<option value='".$row_b['STAFFID']."'>".$row_b['STAFFNAME']."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                 <div class="col">
                    <label>Salary (RM)</label>
                    <input type="number" step="0.01" name="salary" class="form-control" required>
                </div>
                <div class="col">
                    <label>Phone No.</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" name="submit" class="btn btn-primary w-100 fw-bold">Save Staff</button>
            <a href="staff.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>