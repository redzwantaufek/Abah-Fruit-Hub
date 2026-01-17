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
    $sql_del = "DELETE FROM SUPPLIER WHERE SupplierId = :sid";
    $stmt = oci_parse($dbconn, $sql_del);
    oci_bind_by_name($stmt, ":sid", $sid);
    
    if(oci_execute($stmt)){
        echo "<script>Swal.fire('Berjaya!', 'Supplier dipadam.', 'success').then(() => { window.location = 'supplier.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Ralat', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow">🚛 Pengurusan Supplier</h2>
        <a href="supplier_add.php" class="btn btn-warning fw-bold shadow"><i class="fas fa-truck"></i> Daftar Supplier</a>
    </div>

    <div class="glass-card">
        <table class="table table-hover" id="tableSupp">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Syarikat</th>
                    <th>Contact Person</th>
                    <th>No. Telefon</th>
                    <th>Jenis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM SUPPLIER ORDER BY SupplierId ASC";
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . $row['SUPPLIERID'] . "</td>";
                    echo "<td><strong>" . $row['SUPPLIERNAME'] . "</strong></td>";
                    echo "<td>" . $row['SUPPLIERCONTACT'] . "</td>";
                    echo "<td>" . $row['SUPPLIERPHONE'] . "</td>";
                    echo "<td><span class='badge bg-secondary'>" . $row['SUPPLIERTYPE'] . "</span></td>";
                    echo "<td class='text-center'>
                            <button onclick='confirmDelete(\"".$row['SUPPLIERID']."\")' class='btn btn-sm btn-danger'>
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
    $(document).ready(function() { $('#tableSupp').DataTable(); });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Supplier?',
            text: "Data berkaitan akan turut terjejas.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Padam!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'supplier.php?delete_id=' + id;
            }
        })
    }
</script>
</body>
</html>