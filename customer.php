<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 

if(isset($_GET['delete_id'])){
    $cid = $_GET['delete_id'];
    $sql_del = "DELETE FROM CUSTOMER WHERE CustId = :cid";
    $stmt = oci_parse($dbconn, $sql_del);
    oci_bind_by_name($stmt, ":cid", $cid);
    
    if(oci_execute($stmt)){
        echo "<script>Swal.fire('Success!', 'Customer deleted.', 'success').then(() => { window.location = 'customer.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow">👫 Customer Management</h2>
        <a href="customer_add.php" class="btn btn-warning fw-bold shadow"><i class="fas fa-user-plus"></i> Add Customer</a>
    </div>

    <div class="glass-card">
        <table class="table table-hover" id="tableCust">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Customer Name</th>
                    <th>Phone No.</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM CUSTOMER ORDER BY CustId DESC"; 
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . $row['CUSTID'] . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['CUSTNAME']) . "</strong></td>";
                    echo "<td>" . $row['CUSTPHONE'] . "</td>";
                    echo "<td>" . $row['CUSTEMAIL'] . "</td>";
                    echo "<td>" . $row['CUSTADDRESS'] . "</td>";
                    echo "<td class='text-center'>
                            <a href='customer_edit.php?id=".$row['CUSTID']."' class='btn btn-sm btn-primary mb-1'><i class='fas fa-edit'></i></a>
                            <button onclick='confirmDelete(\"".$row['CUSTID']."\")' class='btn btn-sm btn-danger mb-1'><i class='fas fa-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() { $('#tableCust').DataTable(); });
    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Customer?',
            text: "This might delete their purchase history too.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete!'
        }).then((result) => {
            if (result.isConfirmed) { window.location.href = 'customer.php?delete_id=' + id; }
        })
    }
</script>
</body>
</html>