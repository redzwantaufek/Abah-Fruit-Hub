<?php
session_start();
require_once('db_conn.php');

// Security: Admin Only
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
} else if ($_SESSION['user_role'] != 'ADMIN') {
    echo "<script>alert('Akses Ditolak!'); window.location='staff_dashboard.php';</script>";
    exit();
}

include('includes/header.php'); 

// --- Logic Delete ---
if(isset($_GET['delete_id'])){
    $sid = $_GET['delete_id'];
    if($sid == $_SESSION['user_id']) {
        echo "<script>Swal.fire('Ralat', 'Tak boleh padam diri sendiri!', 'error').then(() => { window.location='staff.php'; });</script>";
    } else {
        $sql_del = "DELETE FROM STAFFS WHERE StaffId = :sid";
        $stmt = oci_parse($dbconn, $sql_del);
        oci_bind_by_name($stmt, ":sid", $sid);
        if(oci_execute($stmt)){
            echo "<script>Swal.fire('Berjaya!', 'Staff dipadam.', 'success').then(() => { window.location = 'staff.php'; });</script>";
        } else {
            $e = oci_error($stmt);
            echo "<script>Swal.fire('Ralat', '" . $e['message'] . "', 'error');</script>";
        }
    }
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow">👥 Pengurusan Staff</h2>
        <a href="staff_add.php" class="btn btn-warning fw-bold shadow"><i class="fas fa-user-plus"></i> Tambah Staff</a>
    </div>

    <div class="glass-card">
        <table class="table table-hover" id="tableStaff">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Jawatan</th>
                    <th>Emel</th>
                    <th>No. Telefon</th>
                    <th>Gaji (RM)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM STAFFS ORDER BY StaffId ASC";
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $badge = ($row['STAFFROLE'] == 'ADMIN') ? 'bg-danger' : 'bg-primary';
                    echo "<tr>";
                    echo "<td>" . $row['STAFFID'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['STAFFNAME']) . "</strong></td>";
                    echo "<td><span class='badge $badge'>" . $row['STAFFROLE'] . "</span></td>";
                    echo "<td>" . $row['STAFFEMAIL'] . "</td>";
                    echo "<td>" . $row['STAFFPHONE'] . "</td>";
                    echo "<td>" . number_format($row['STAFFSALARY'], 2) . "</td>";
                    echo "<td class='text-center'>
                         <a href='staff_edit.php?id=".$row['STAFFID']."' class='btn btn-sm btn-primary mb-1'>
                         <i class='fas fa-edit'></i>
                         </a>
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
            title: 'Hapus Pekerja?',
            text: "Adakah anda pasti?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Padam!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'staff.php?delete_id=' + id;
            }
        })
    }
</script>
</body>
</html>