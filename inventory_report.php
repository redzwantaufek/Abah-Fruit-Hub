<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-warehouse me-2"></i>Inventory Report</h2>
        <a href="report_list.php" class="btn btn-light text-primary fw-bold shadow-sm rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Back to Hub
        </a>
    </div>

    <div class="glass-card p-5">
        <div class="text-center mb-5 border-bottom pb-4">
            <h2 class="fw-bold text-uppercase text-dark"><i class="fas fa-clipboard-list me-2"></i>Stock Status Report</h2>
            <p class="text-muted mb-0">Overview of current inventory levels and expiry status.</p>
            <p class="small text-primary fw-bold">Generated on: <?php echo date('d F Y, h:i A'); ?></p>
        </div>

        <div class="row mb-4 align-items-center justify-content-between d-print-none">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Display</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px; cursor: pointer;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
                <span class="text-muted fw-bold small ms-2">items</span>
            </div>
            <div class="col-md-5">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search product name or category...">
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableInv">
            <thead class="table-light">
                <tr>
                    <th class="text-center">ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th class="text-center">Stock Level</th> <th>Expiry Date</th>
                    <th class="text-center">Inventory Health</th> </tr>
            </thead>
            <tbody>
                <?php
                // Kita ambil SEMUA buah, tapi sort ikut stok paling sikit dulu
                $sql = "SELECT * FROM FRUITS ORDER BY QuantityStock ASC";
                
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $stock = $row['QUANTITYSTOCK'];
                    $expDate = strtotime($row['EXPIREDATE']);
                    $today = time();
                    $daysDiff = ($expDate - $today) / (60 * 60 * 24); // Kira beza hari

                    // Logic Status & Warna (Inventory Health)
                    $statusBadge = "";
                    $rowClass = ""; // Boleh guna untuk highlight row jika perlu

                    // 1. Check Expired Dulu (Paling Kritikal)
                    if ($daysDiff < 0) {
                        $statusBadge = "<span class='badge bg-dark rounded-pill px-3'><i class='fas fa-skull-crossbones me-1'></i> EXPIRED</span>";
                        $stockColor = "text-muted text-decoration-line-through";
                    } 
                    // 2. Check Nak Expire (Warning)
                    elseif ($daysDiff < 7) {
                        $statusBadge = "<span class='badge bg-warning text-dark rounded-pill px-3'><i class='fas fa-clock me-1'></i> EXPIRING SOON</span>";
                        $stockColor = "text-dark";
                    } 
                    // 3. Check Stok Habis/Sikit
                    elseif ($stock <= 5) {
                        $statusBadge = "<span class='badge bg-danger rounded-pill px-3'><i class='fas fa-battery-empty me-1'></i> CRITICAL LOW</span>";
                        $stockColor = "text-danger fw-bold";
                    } 
                    elseif ($stock <= 15) {
                        $statusBadge = "<span class='badge bg-info text-dark rounded-pill px-3'><i class='fas fa-battery-quarter me-1'></i> LOW STOCK</span>";
                        $stockColor = "text-primary fw-bold";
                    } 
                    else {
                        $statusBadge = "<span class='badge bg-success rounded-pill px-3'><i class='fas fa-check-circle me-1'></i> HEALTHY</span>";
                        $stockColor = "text-success fw-bold";
                    }

                    echo "<tr>";
                    echo "<td class='text-center text-muted small'>" . $row['FRUITID'] . "</td>";
                    
                    // Column Nama Produk
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='bg-light rounded-circle p-2 me-2 d-flex justify-content-center align-items-center' style='width:35px;height:35px;'>
                                    <i class='fas fa-apple-alt text-secondary'></i>
                                </div>
                                <span class='fw-bold'>" . htmlspecialchars($row['FRUITNAME']) . "</span>
                            </div>
                          </td>";
                    
                    echo "<td>" . $row['CATEGORY'] . "</td>";
                    
                    // Column Stock Level
                    echo "<td class='text-center $stockColor' style='font-size:1.1rem;'>" . $stock . "</td>";
                    
                    // Column Expiry
                    echo "<td>" . date('d M Y', $expDate) . " <br><small class='text-muted'>(" . ceil($daysDiff) . " days left)</small></td>";
                    
                    // Column Status
                    echo "<td class='text-center'>" . $statusBadge . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
            <small class="text-muted">FruitHub Management System v2.0</small>
            <button onclick="window.print()" class="btn btn-primary fw-bold px-4 shadow rounded-pill d-print-none">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#tableInv').DataTable({
            "dom": "rtip", // Kunci untuk elak double search bar
            "pageLength": 10,
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ products</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });
</script>
</body>
</html>