<?php
session_start();
require_once('db_conn.php');

// Security: Asalkan user dah login (tak kira role apa), dia boleh masuk.
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-boxes me-2"></i>Fruit Inventory</h2>
        
        <a href="fruits_add.php" class="btn btn-warning fw-bold shadow-sm px-4 rounded-pill">
            <i class="fas fa-plus me-2"></i>Add New Fruit
        </a>
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
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search fruit name...">
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableFruit">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = "SELECT * FROM FRUITS ORDER BY FruitId ASC";
                $s = oci_parse($dbconn, $q);
                oci_execute($s);

                while ($row = oci_fetch_assoc($s)) {
                    $stock = $row['QUANTITYSTOCK'];
                    
                    // Logic Gambar
                    $img = "https://via.placeholder.com/50?text=Fruit"; 
                    $imgKey = $row['IMAGEURL'] ?? $row['ImageURL'] ?? ''; 
                    if (!empty($imgKey) && file_exists("assets/img/" . $imgKey)) {
                        $img = "assets/img/" . $imgKey;
                    }

                    $status = ($stock < 10) ? '<span class="badge bg-danger">LOW</span>' : '<span class="badge bg-success">OK</span>';
                    
                    echo "<tr>";
                    echo "<td><img src='$img' class='rounded border shadow-sm' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                    echo "<td class='fw-bold'>" . htmlspecialchars($row['FRUITNAME']) . "</td>";
                    echo "<td>RM " . number_format($row['FRUITPRICE'], 2) . "</td>";
                    echo "<td>" . $stock . "</td>";
                    echo "<td>" . $status . "</td>";
                    
                    // COLUMN ACTION: Bebas untuk semua
                    echo "<td class='text-center'>
                            <a href='fruits_edit.php?id=".$row['FRUITID']."' class='btn btn-sm btn-light text-primary rounded-circle shadow-sm'><i class='fas fa-pen'></i></a>
                            <button class='btn btn-sm btn-light text-danger rounded-circle ms-1 shadow-sm' onclick='delItem(".$row['FRUITID'].")'><i class='fas fa-trash'></i></button>
                          </td>";
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
        "dom": "rtip", "pageLength": 10,
        "columnDefs": [{ "orderable": false, "targets": 0 }],
        "language": { "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" } }
    });
    $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
    $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
});

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