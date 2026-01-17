<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include('includes/header.php'); 
?>

<div class="container">
    <h2 class="mb-4 text-white fw-bold text-shadow">📜 Sejarah Jualan (Orders)</h2>

    <div class="glass-card">
        <table class="table table-hover" id="tableOrder">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Tarikh</th>
                    <th>Pelanggan</th>
                    <th>Staff</th>
                    <th>Jumlah (RM)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT o.OrderId, o.OrderDate, o.TotalAmount, o.OrderStatus,
                               c.CustName, s.StaffName
                        FROM ORDERS o
                        JOIN CUSTOMER c ON o.CustId = c.CustId
                        LEFT JOIN STAFFS s ON o.StaffId = s.StaffId
                        ORDER BY o.OrderId DESC";
                
                $stmt = oci_parse($dbconn, $sql);
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    echo "<tr>";
                    echo "<td>#" . $row['ORDERID'] . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($row['ORDERDATE'])) . "</td>";
                    echo "<td><strong>" . $row['CUSTNAME'] . "</strong></td>";
                    echo "<td>" . ($row['STAFFNAME'] ? $row['STAFFNAME'] : 'Staff Berhenti') . "</td>";
                    echo "<td>RM " . number_format($row['TOTALAMOUNT'], 2) . "</td>";
                    echo "<td><span class='badge bg-success'>" . $row['ORDERSTATUS'] . "</span></td>";
                    echo "<td>
                            <a href='order_details.php?id=".$row['ORDERID']."' class='btn btn-sm btn-info text-white'>
                                <i class='fas fa-eye'></i> Lihat Item
                            </a>
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
        $('#tableOrder').DataTable({
            order: [[0, 'desc']] // Susun Order ID paling baru di atas
        });
    });
</script>
</body>
</html>