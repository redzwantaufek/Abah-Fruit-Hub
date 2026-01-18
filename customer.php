<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 

// Logic Delete
if(isset($_GET['delete_id'])){
    $cid = $_GET['delete_id'];
    $sql_del = "DELETE FROM CUSTOMER WHERE CustId = :cid";
    $stmt = oci_parse($dbconn, $sql_del);
    oci_bind_by_name($stmt, ":cid", $cid);
    if(oci_execute($stmt)){
        echo "<script>Swal.fire('Deleted!', 'Customer removed.', 'success').then(() => { window.location = 'customer.php'; });</script>";
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-users me-2"></i>Customers</h2>
        <a href="customer_add.php" class="btn btn-warning fw-bold shadow-sm px-4 rounded-pill">
            <i class="fas fa-user-plus me-2"></i>Add Customer
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
                <span class="text-muted fw-bold small ms-2">rows</span>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search customer...">
                    <button class="btn btn-primary px-4 fw-bold" style="background: linear-gradient(45deg, #00c6ff, #0072ff); border: none;">Search</button>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableCust">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Phone No.</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM CUSTOMER ORDER BY CustId DESC"; 
                $stmt = oci_parse($dbconn, $sql); oci_execute($stmt);
                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    echo "<tr>";
                    echo "<td class='text-muted small'>" . $row['CUSTID'] . "</td>";
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='bg-light rounded-circle p-2 me-3 shadow-sm d-flex justify-content-center align-items-center' style='width:40px;height:40px;'>
                                    <i class='fas fa-user text-info'></i>
                                </div>
                                <strong>" . htmlspecialchars($row['CUSTNAME']) . "</strong>
                            </div>
                          </td>";
                    echo "<td>" . $row['CUSTPHONE'] . "</td>";
                    echo "<td>" . ($row['CUSTEMAIL'] ? $row['CUSTEMAIL'] : '<span class="text-muted">-</span>') . "</td>";
                    echo "<td class='small text-truncate' style='max-width: 200px;'>" . htmlspecialchars($row['CUSTADDRESS']) . "</td>";
                    echo "<td class='text-center'>
                            <a href='customer_edit.php?id=".$row['CUSTID']."' class='btn btn-sm btn-light text-primary shadow-sm rounded-circle' style='width:35px;height:35px;'><i class='fas fa-pen'></i></a>
                            <button onclick='confirmDelete(\"".$row['CUSTID']."\")' class='btn btn-sm btn-light text-danger shadow-sm rounded-circle ms-1' style='width:35px;height:35px;'><i class='fas fa-trash'></i></button>
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
        var table = $('#tableCust').DataTable({
            "dom": "rtip", // Hide default controls
            "pageLength": 10,
            "columnDefs": [{ "orderable": false, "targets": [5] }],
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ customers</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?', text: "Delete this customer?", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'customer.php?delete_id=' + id; } })
    }
</script>
</body>
</html>