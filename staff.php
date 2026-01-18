<?php
session_start();
require_once('db_conn.php');

// Security Check
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
} else if ($_SESSION['user_role'] != 'ADMIN') { 
    echo "<script>alert('Access Denied!'); window.location='staff_dashboard.php';</script>"; 
    exit(); 
}

include('includes/header.php'); 

// --- Logic Delete ---
if(isset($_GET['delete_id'])){
    $sid = $_GET['delete_id'];
    if($sid == $_SESSION['user_id']) {
        echo "<script>Swal.fire('Error', 'You cannot delete yourself!', 'error').then(() => { window.location='staff.php'; });</script>";
    } else {
        $sql_del = "DELETE FROM STAFFS WHERE StaffId = :sid";
        $stmt = oci_parse($dbconn, $sql_del);
        oci_bind_by_name($stmt, ":sid", $sid);
        if(oci_execute($stmt)){
            echo "<script>Swal.fire('Success!', 'Staff record deleted.', 'success').then(() => { window.location = 'staff.php'; });</script>";
        } else {
            $e = oci_error($stmt);
            echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
        }
    }
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow">👥 Staff Management</h2>
        <a href="staff_add.php" class="btn btn-warning fw-bold shadow"><i class="fas fa-user-plus"></i> Add New Staff</a>
    </div>

    <div class="glass-card">
        <table class="table table-hover" id="tableStaff">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Reports To (Manager)</th>
                    <th>Phone No.</th>
                    <th>Salary (RM)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // SQL QUERY: Kita guna alias "NAMABOSS"
                $sql = "SELECT s1.*, s2.StaffName AS \"NAMABOSS\" 
                        FROM STAFFS s1 
                        LEFT JOIN STAFFS s2 ON s1.ManagerId = s2.StaffId 
                        ORDER BY s1.StaffId ASC";
                
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                // PENYELESAIAN MASALAH: Tambah 'OCI_RETURN_NULLS'
                // Ini memaksa PHP terima key 'NAMABOSS' walaupun nilainya NULL
                while ($row = oci_fetch_array($stmt, OCI_ASSOC + OCI_RETURN_NULLS)) {
                    
                    $badge = ($row['STAFFROLE'] == 'ADMIN') ? 'bg-danger' : 'bg-primary';
                    
                    // Semak jika NAMABOSS wujud dan tidak kosong
                    $nama_boss = (isset($row['NAMABOSS']) && !empty($row['NAMABOSS'])) ? $row['NAMABOSS'] : '-';

                    echo "<tr>";
                    echo "<td>" . $row['STAFFID'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['STAFFNAME']) . "</strong></td>";
                    echo "<td><span class='badge $badge'>" . $row['STAFFROLE'] . "</span></td>";
                    
                    // Column Manager
                    echo "<td>";
                    if($nama_boss != '-') {
                        echo "<i class='fas fa-user-tie text-muted me-1'></i> " . $nama_boss;
                    } else {
                        echo "-";
                    }
                    echo "</td>";
                    
                    echo "<td>" . $row['STAFFPHONE'] . "</td>";
                    echo "<td>" . number_format($row['STAFFSALARY'], 2) . "</td>";
                    echo "<td class='text-center'>
                            <a href='staff_edit.php?id=".$row['STAFFID']."' class='btn btn-sm btn-primary mb-1'><i class='fas fa-edit'></i></a>
                            <button onclick='confirmDelete(\"".$row['STAFFID']."\")' class='btn btn-sm btn-danger mb-1'>
                                <i class='fas fa-trash'></i>
                            </button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() { $('#tableStaff').DataTable(); });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Staff?',
            text: "Are you sure you want to delete this employee?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete!'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'staff.php?delete_id=' + id; }
        })
    }
</script>
</body>
</html>