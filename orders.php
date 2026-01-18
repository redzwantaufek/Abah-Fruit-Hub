<?php
session_start();
require_once('db_conn.php');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 
?>

<div class="container">
    <h2 class="mb-4 text-white fw-bold text-shadow">📜 Sales History</h2>

    <div class="glass-card mb-3 p-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="fw-bold">From Date:</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="fw-bold">To Date:</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $_GET['end_date'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                <?php if(isset($_GET['start_date'])){ ?>
                    <a href="orders.php" class="btn btn-secondary w-100 mt-2">Reset</a>
                <?php } ?>
            </div>
        </form>
    </div>

    <div class="glass-card">
        <table class="table table-hover" id="tableOrder">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Staff</th>
                    <th>Total (RM)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT o.OrderId, o.OrderDate, o.TotalAmount, o.OrderStatus, c.CustName, s.StaffName
                        FROM ORDERS o
                        JOIN CUSTOMER c ON o.CustId = c.CustId
                        LEFT JOIN STAFFS s ON o.StaffId = s.StaffId ";
                
                if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    $start = $_GET['start_date']; $end = $_GET['end_date'];
                    $sql .= " WHERE TRUNC(o.OrderDate) BETWEEN TO_DATE(:start_d, 'YYYY-MM-DD') AND TO_DATE(:end_d, 'YYYY-MM-DD') ";
                }
                $sql .= " ORDER BY o.OrderId DESC";
                
                $stmt = oci_parse($dbconn, $sql);
                if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    oci_bind_by_name($stmt, ":start_d", $start); oci_bind_by_name($stmt, ":end_d", $end);
                }
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    echo "<tr>";
                    echo "<td>#" . $row['ORDERID'] . "</td>";
                    echo "<td>" . date('d-m-Y', strtotime($row['ORDERDATE'])) . "</td>";
                    echo "<td><strong>" . $row['CUSTNAME'] . "</strong></td>";
                    echo "<td>" . ($row['STAFFNAME'] ? $row['STAFFNAME'] : 'N/A') . "</td>";
                    echo "<td>RM " . number_format($row['TOTALAMOUNT'], 2) . "</td>";
                    echo "<td><span class='badge bg-success'>" . $row['ORDERSTATUS'] . "</span></td>";
                    echo "<td><a href='order_details.php?id=".$row['ORDERID']."' class='btn btn-sm btn-info text-white'><i class='fas fa-eye'></i> View Items</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<script> $(document).ready(function() { $('#tableOrder').DataTable({ order: [[0, 'desc']] }); }); </script>
</body>
</html>