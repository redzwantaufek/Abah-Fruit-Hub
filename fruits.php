<?php
session_start();
require_once('db_conn.php');

// Security: Login Required
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php'); 

// --- Logic Delete ---
if(isset($_GET['delete_id'])){
    $fid = $_GET['delete_id'];
    $sql_del = "DELETE FROM FRUITS WHERE FruitId = :fid";
    $stmt = oci_parse($dbconn, $sql_del);
    oci_bind_by_name($stmt, ":fid", $fid);
    
    if(oci_execute($stmt)){
        echo "<script>
        Swal.fire('Berjaya!', 'Data buah telah dipadam.', 'success').then(() => { window.location = 'fruits.php'; });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Ralat', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow">📦 Pengurusan Stok Buah</h2>
        <a href="fruits_add.php" class="btn btn-warning fw-bold shadow"><i class="fas fa-plus"></i> Tambah Buah</a>
    </div>

    <div class="glass-card">
        <table class="table table-hover table-bordered" id="tableBuah">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Buah</th>
                    <th>Harga (RM)</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Luput</th>
                    <th>Supplier</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT f.*, s.SupplierName 
                        FROM FRUITS f 
                        LEFT JOIN SUPPLIER s ON f.SupplierId = s.SupplierId
                        ORDER BY f.FruitId ASC";
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $expDate = isset($row['EXPIREDATE']) ? date('d-m-Y', strtotime($row['EXPIREDATE'])) : '-';
                    $stok_class = ($row['QUANTITYSTOCK'] < 10) ? 'text-danger fw-bold' : '';

                    echo "<tr>";
                    echo "<td>" . $row['FRUITID'] . "</td>";
                    echo "<td><strong>" . $row['FRUITNAME'] . "</strong></td>";
                    echo "<td>" . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td class='$stok_class'>" . $row['QUANTITYSTOCK'] . "</td>";
                    echo "<td><span class='badge bg-info'>" . $row['CATEGORY'] . "</span></td>";
                    echo "<td>" . $expDate . "</td>";
                    echo "<td>" . $row['SUPPLIERNAME'] . "</td>";
                    echo "<td class='text-center'>
                            <a href='fruits_edit.php?id=".$row['FRUITID']."' class='btn btn-sm btn-primary mb-1'><i class='fas fa-edit'></i></a>
                            <button onclick='confirmDelete(\"".$row['FRUITID']."\")' class='btn btn-sm btn-danger mb-1'>
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
    $(document).ready(function() { $('#tableBuah').DataTable(); });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Anda Pasti?',
            text: "Data ini akan dihapuskan kekal.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Padam!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'fruits.php?delete_id=' + id;
            }
        })
    }
</script>
</body>
</html>