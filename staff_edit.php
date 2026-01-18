<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

include('includes/header.php');

// 1. Get Current Data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM STAFFS WHERE StaffId = :id";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    
    if(!$row) {
        echo "<script>alert('Staff not found!'); window.location='staff.php';</script>";
        exit();
    }
}

// 2. Pre-load Managers
$sql_boss = "SELECT StaffId, StaffName FROM STAFFS WHERE StaffRole = 'ADMIN' ORDER BY StaffName ASC";
$stmt_boss = oci_parse($dbconn, $sql_boss);
oci_execute($stmt_boss);

// 3. Update Process
if (isset($_POST['update'])) {
    $sid = $_POST['sid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $salary = $_POST['salary'];
    $addr = $_POST['address'];
    
    $mgr_id = $_POST['manager_id'];
    if($mgr_id == "") $mgr_id = null;

    $sql_upd = "UPDATE STAFFS SET StaffName=:nm, StaffEmail=:em, StaffPhone=:ph, StaffRole=:rl, StaffSalary=:sal, StaffAddress=:ad, ManagerId=:mgr 
                WHERE StaffId=:sid";
    
    $stmt = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":em", $email);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":rl", $role);
    oci_bind_by_name($stmt, ":sal", $salary);
    oci_bind_by_name($stmt, ":ad", $addr);
    oci_bind_by_name($stmt, ":mgr", $mgr_id);
    oci_bind_by_name($stmt, ":sid", $sid);

    if (oci_execute($stmt)) {
        echo "<script>
            Swal.fire('Success!', 'Staff information updated.', 'success').then(() => {
                window.location = 'staff.php';
            });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-3 text-primary fw-bold">✏️ Edit Staff</h3>
        
        <form method="POST">
            <input type="hidden" name="sid" value="<?php echo $row['STAFFID']; ?>">
            
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $row['STAFFNAME']; ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $row['STAFFEMAIL']; ?>" required>
                </div>
                <div class="col">
                    <label>Phone No.</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $row['STAFFPHONE']; ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>Role</label>
                    <select name="role" class="form-select">
                        <option value="STAFF" <?php if($row['STAFFROLE']=='STAFF') echo 'selected'; ?>>Regular Staff</option>
                        <option value="ADMIN" <?php if($row['STAFFROLE']=='ADMIN') echo 'selected'; ?>>Admin/Manager</option>
                    </select>
                </div>
                
                <div class="col">
                    <label>Reports To (Manager)</label>
                    <select name="manager_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php
                        while ($b = oci_fetch_array($stmt_boss, OCI_ASSOC)) {
                            $selected = ($b['STAFFID'] == $row['MANAGERID']) ? "selected" : "";
                            if ($b['STAFFID'] != $row['STAFFID']) {
                                echo "<option value='".$b['STAFFID']."' $selected>".$b['STAFFNAME']."</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label>Salary (RM)</label>
                <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo $row['STAFFSALARY']; ?>" required>
            </div>

            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo $row['STAFFADDRESS']; ?></textarea>
            </div>

            <button type="submit" name="update" class="btn btn-primary w-100 fw-bold">Save Changes</button>
            <a href="staff.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>