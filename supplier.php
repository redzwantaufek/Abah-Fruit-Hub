<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php'); 

// Logic Delete
if(isset($_GET['delete_id'])){
    $sid = $_GET['delete_id'];
    
    // Kita perlu delete child table dulu sebelum parent (Foreign Key constraint)
    $sql_check = "SELECT SupplierType FROM SUPPLIER WHERE SupplierId = :sid";
    $stmt_check = oci_parse($dbconn, $sql_check);
    oci_bind_by_name($stmt_check, ":sid", $sid);
    oci_execute($stmt_check);
    $row = oci_fetch_array($stmt_check, OCI_ASSOC);
    
    if($row){
        if($row['SUPPLIERTYPE'] == 'LOCALFARM') {
            $del_child = oci_parse($dbconn, "DELETE FROM LOCALFARM WHERE SupplierId = :sid");
        } else {
            $del_child = oci_parse($dbconn, "DELETE FROM DISTRIBUTOR WHERE SupplierId = :sid");
        }
        oci_bind_by_name($del_child, ":sid", $sid);
        oci_execute($del_child, OCI_NO_AUTO_COMMIT);
    }

    $sql_del = "DELETE FROM SUPPLIER WHERE SupplierId = :sid";
    $stmt = oci_parse($dbconn, $sql_del);
    oci_bind_by_name($stmt, ":sid", $sid);
    
    if(oci_execute($stmt, OCI_COMMIT_ON_SUCCESS)){
        echo "<script>Swal.fire('Deleted!', 'Supplier has been removed.', 'success').then(() => { window.location = 'supplier.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-truck me-2"></i>Supplier Management</h2>
        <a href="supplier_add.php" class="btn btn-warning fw-bold shadow-sm px-4 rounded-pill">
            <i class="fas fa-plus me-2"></i>Register Supplier
        </a>
    </div>

    <div class="glass-card p-4">
        <div class="row mb-4 align-items-center justify-content-between">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Show</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                </select>
                <span class="text-muted fw-bold small ms-2">suppliers</span>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search supplier company...">
                    <button class="btn btn-primary px-4 fw-bold" style="background: linear-gradient(45deg, #11998e, #38ef7d); border: none;">Search</button>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableSupp">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Contact Info</th>
                    <th>Type</th>
                    <th>Location / Details</th> 
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // JOIN Table untuk dapatkan alamat (Farm) atau Info (Distributor)
                $sql = "SELECT s.*, 
                               l.FarmAddress, 
                               d.LogisticPartner, 
                               d.DistributionCenterId 
                        FROM SUPPLIER s
                        LEFT JOIN LOCALFARM l ON s.SupplierId = l.SupplierId
                        LEFT JOIN DISTRIBUTOR d ON s.SupplierId = d.SupplierId
                        ORDER BY s.SupplierId ASC";
                        
                $stmt = oci_parse($dbconn, $sql); 
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $typeClass = ($row['SUPPLIERTYPE'] == 'LOCALFARM') ? 'bg-success' : 'bg-info text-dark';
                    
                    // Logic untuk paparan Lokasi
                    $locationInfo = '-';
                    if ($row['SUPPLIERTYPE'] == 'LOCALFARM') {
                        // Papar Alamat Kebun dengan ikon peta
                        $addr = $row['FARMADDRESS'] ? $row['FARMADDRESS'] : 'No Address';
                        $locationInfo = '<small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> ' . htmlspecialchars($addr) . '</small>';
                    } else {
                        // Papar Partner Logistik & Center ID
                        $log = $row['LOGISTICPARTNER'] ? $row['LOGISTICPARTNER'] : '-';
                        $cen = $row['DISTRIBUTIONCENTERID'] ? $row['DISTRIBUTIONCENTERID'] : '-';
                        $locationInfo = '<small class="text-muted d-block"><i class="fas fa-shipping-fast text-info me-1"></i> ' . htmlspecialchars($log) . '</small>
                                         <small class="text-muted fw-bold" style="font-size:0.75rem;">Center: ' . htmlspecialchars($cen) . '</small>';
                    }

                    echo "<tr>";
                    echo "<td class='text-muted small'>" . $row['SUPPLIERID'] . "</td>";
                    
                    // Column Company Name
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='bg-light rounded-circle p-2 me-3 shadow-sm d-flex justify-content-center align-items-center' style='width:40px;height:40px;'>
                                    <i class='fas fa-building text-success'></i>
                                </div>
                                <strong>" . htmlspecialchars($row['SUPPLIERNAME']) . "</strong>
                            </div>
                          </td>";
                    
                    // Gabungkan Phone & Contact Person jadi satu column supaya jimat ruang
                    echo "<td>
                            <div class='d-flex flex-column'>
                                <span class='fw-bold text-dark'>" . htmlspecialchars($row['SUPPLIERCONTACT']) . "</span>
                                <small class='text-muted'><i class='fas fa-phone-alt me-1' style='font-size:0.7rem;'></i> " . $row['SUPPLIERPHONE'] . "</small>
                            </div>
                          </td>";

                    echo "<td><span class='badge $typeClass rounded-pill px-3 shadow-sm'>" . $row['SUPPLIERTYPE'] . "</span></td>";
                    
                    // Papar column baru
                    echo "<td>" . $locationInfo . "</td>";
                    
                    echo "<td class='text-center'>
                            <a href='supplier_edit.php?id=".$row['SUPPLIERID']."' class='btn btn-sm btn-light text-primary shadow-sm rounded-circle' style='width:35px;height:35px;'><i class='fas fa-pen'></i></a>
                            <button onclick='confirmDelete(\"".$row['SUPPLIERID']."\")' class='btn btn-sm btn-light text-danger shadow-sm rounded-circle ms-1' style='width:35px;height:35px;'><i class='fas fa-trash'></i></button>
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
        var table = $('#tableSupp').DataTable({
            "dom": "rtip",
            "pageLength": 10,
            "columnDefs": [{ "orderable": false, "targets": [5] }], // Action column no sort
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ suppliers</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This supplier and its details will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'supplier.php?delete_id=' + id; } })
    }
</script>
</body>
</html>