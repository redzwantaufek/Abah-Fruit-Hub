<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

// Check adakah user ini ADMIN?
$isAdmin = ($_SESSION['user_role'] == 'ADMIN');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-boxes me-2"></i>Fruit Inventory</h2>
        
        <?php if ($isAdmin): ?>
        <a href="fruits_add.php" class="btn btn-warning fw-bold shadow-sm px-4 rounded-pill">
            <i class="fas fa-plus me-2"></i>Add New Fruit
        </a>
        <?php endif; ?>
    </div>

    <div class="glass-card p-4">
        <div class="row mb-4 align-items-center justify-content-between">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Show</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-muted fw-bold small ms-2">items</span>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search fruit name, category...">
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableFruit">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price (RM)</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <?php if ($isAdmin) { echo '<th class="text-center">Action</th>'; } ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = "SELECT * FROM FRUITS ORDER BY FruitId ASC";
                $s = oci_parse($dbconn, $q);
                oci_execute($s);

                while ($row = oci_fetch_assoc($s)) {
                    $stock = $row['QUANTITYSTOCK'];
                    $expDate = strtotime($row['EXPIREDATE']);
                    $daysLeft = ($expDate - time()) / (60 * 60 * 24);

                    // Logic Status Badge
                    if ($daysLeft < 0) {
                        $status = '<span class="badge bg-dark rounded-pill">EXPIRED</span>';
                        $rowClass = "text-muted";
                    } elseif ($stock < 5) {
                        $status = '<span class="badge bg-danger rounded-pill">CRITICAL</span>';
                        $rowClass = "fw-bold text-danger";
                    } elseif ($stock < 15) {
                        $status = '<span class="badge bg-warning text-dark rounded-pill">LOW</span>';
                        $rowClass = "";
                    } else {
                        $status = '<span class="badge bg-success rounded-pill">OK</span>';
                        $rowClass = "";
                    }

                    echo "<tr>";
                    echo "<td class='small text-muted'>" . $row['FRUITID'] . "</td>";
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='bg-light rounded-circle p-2 me-2 shadow-sm d-flex justify-content-center align-items-center' style='width:35px;height:35px;'>
                                    <i class='fas fa-apple-alt text-success'></i>
                                </div>
                                <span class='fw-bold $rowClass'>" . htmlspecialchars($row['FRUITNAME']) . "</span>
                            </div>
                          </td>";
                    echo "<td>" . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td class='$rowClass'>" . $stock . "</td>";
                    echo "<td><span class='badge bg-info text-dark bg-opacity-25 border border-info'>" . $row['CATEGORY'] . "</span></td>";
                    echo "<td>" . date('d M Y', $expDate) . "</td>";
                    echo "<td>" . $status . "</td>";
                    
                    // SEL ACTION: HANYA PAPAR JIKA ADMIN
                    if ($isAdmin) {
                        echo "<td class='text-center'>
                                <a href='fruits_edit.php?id=".$row['FRUITID']."' class='btn btn-sm btn-light text-primary shadow-sm rounded-circle' style='width:32px;height:32px;padding:0;line-height:32px;'><i class='fas fa-pen'></i></a>
                                <button onclick='delItem(".$row['FRUITID'].")' class='btn btn-sm btn-light text-danger shadow-sm rounded-circle ms-1' style='width:32px;height:32px;padding:0;line-height:32px;'><i class='fas fa-trash'></i></button>
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
        "dom": "rtip",
        "pageLength": 10,
        // Kita buang setting columnDefs yang rigid supaya tak error bila column kurang
        "ordering": true,
        "language": {
            "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ items</span>",
            "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
        }
    });

    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
    $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
});

// Fungsi Delete (Hanya akan dipanggil oleh butang yang wujud untuk Admin)
function delItem(id) {
    Swal.fire({
        title: 'Delete Item?', text: "Irreversible action!", icon: 'warning', 
        showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
    }).then((result) => { 
        if (result.isConfirmed) { window.location.href = 'fruits_delete.php?id=' + id; } 
    })
}
</script>
</body>
</html>