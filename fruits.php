<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 

// Logic Delete
if(isset($_GET['delete_id'])){
    $fid = $_GET['delete_id'];
    $sql_del = "DELETE FROM FRUITS WHERE FruitId = :fid";
    $stmt = oci_parse($dbconn, $sql_del);
    oci_bind_by_name($stmt, ":fid", $fid);
    if(oci_execute($stmt)){
        echo "<script>Swal.fire('Deleted!', 'Fruit item has been removed.', 'success').then(() => { window.location = 'fruits.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-boxes me-2"></i>Fruit Inventory</h2>
        <?php if($_SESSION['user_role'] == 'ADMIN') { ?>
        <a href="fruits_add.php" class="btn btn-warning fw-bold shadow-sm px-4 rounded-pill">
            <i class="fas fa-plus-circle me-2"></i>Add New Fruit
        </a>
        <?php } ?>
    </div>

    <div class="glass-card p-4">
        
        <div class="row mb-4 align-items-center justify-content-between">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Show</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px; cursor: pointer;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-muted fw-bold small ms-2">rows</span>
            </div>

            <div class="col-md-5 mt-3 mt-md-0">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search fruit...">
                    <button class="btn btn-primary px-4 fw-bold" style="background: linear-gradient(45deg, #23a6d5, #23d5ab); border: none;">Search</button>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableFruit">
            <thead class="table-dark">
                <tr>
                    <th class="text-center">ID</th>
                    <th>Fruit Name</th>
                    <th>Price (RM)</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Expiry Date</th>
                    <th>Supplier</th>
                    <?php if($_SESSION['user_role'] == 'ADMIN') { ?><th class="text-center">Action</th><?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT f.*, s.SupplierName FROM FRUITS f LEFT JOIN SUPPLIER s ON f.SupplierId = s.SupplierId ORDER BY f.FruitId ASC";
                $stmt = oci_parse($dbconn, $sql); oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $stockClass = ($row['QUANTITYSTOCK'] < 10) ? 'text-danger fw-bold' : 'fw-bold text-dark';
                    $daysToExpire = (strtotime($row['EXPIREDATE']) - time()) / (60 * 60 * 24);
                    $dateClass = ($daysToExpire < 7) ? 'text-warning fw-bold' : '';

                    echo "<tr>";
                    echo "<td class='text-center text-muted small'>" . $row['FRUITID'] . "</td>";
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='bg-light rounded-circle p-2 me-3 shadow-sm d-flex justify-content-center align-items-center' style='width:40px;height:40px;'>
                                    <i class='fas fa-apple-alt text-success fa-lg'></i>
                                </div>
                                <span class='fw-bold text-dark'>" . htmlspecialchars($row['FRUITNAME']) . "</span>
                            </div>
                          </td>";
                    echo "<td>RM " . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td class='$stockClass'>" . $row['QUANTITYSTOCK'] . "</td>";
                    
                    $catBadge = ($row['CATEGORY'] == 'LOCAL') ? 'bg-success bg-opacity-75' : 'bg-info bg-opacity-75 text-dark';
                    echo "<td><span class='badge $catBadge rounded-pill px-3 shadow-sm'>" . $row['CATEGORY'] . "</span></td>";
                    echo "<td class='$dateClass'>" . date('d M Y', strtotime($row['EXPIREDATE'])) . "</td>";
                    echo "<td class='small text-muted'>" . htmlspecialchars($row['SUPPLIERNAME']) . "</td>";
                    
                    if($_SESSION['user_role'] == 'ADMIN') {
                        echo "<td class='text-center'>
                                <a href='fruits_edit.php?id=".$row['FRUITID']."' class='btn btn-sm btn-light text-primary shadow-sm rounded-circle' style='width:35px; height:35px;'><i class='fas fa-pen'></i></a>
                                <button onclick='confirmDelete(\"".$row['FRUITID']."\")' class='btn btn-sm btn-light text-danger shadow-sm rounded-circle ms-2' style='width:35px; height:35px;'><i class='fas fa-trash'></i></button>
                              </td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#tableFruit').DataTable({
            "dom": "rtip", // INI PENTING: Sembunyikan search bar default yang buruk tu
            "pageLength": 10,
            "columnDefs": [{ "orderable": false, "targets": [7] }],
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ items</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });

        // Sambung Custom Search & Dropdown
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Item?', text: "Cannot be undone!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'fruits.php?delete_id=' + id; } })
    }
</script>
</body>
</html>