<?php
session_start();
require_once('db_conn.php');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-history me-2"></i>Sales History</h2>
    </div>

    <div class="glass-card p-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="fw-bold text-muted small">From Date:</label>
                <input type="date" name="start_date" class="form-control shadow-sm" value="<?php echo $_GET['start_date'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="fw-bold text-muted small">To Date:</label>
                <input type="date" name="end_date" class="form-control shadow-sm" value="<?php echo $_GET['end_date'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm"><i class="fas fa-filter me-2"></i> Filter Records</button>
            </div>
        </form>
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
                <span class="text-muted fw-bold small ms-2">orders</span>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search Order ID, Name...">
                    <button class="btn btn-primary px-4 fw-bold" style="background: linear-gradient(45deg, #f093fb, #f5576c); border: none;">Search</button>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableOrders">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Payment</th> <th>Staff</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ambil column PaymentMethod
                $sql = "SELECT o.OrderId, o.OrderDate, o.TotalAmount, o.OrderStatus, o.PaymentMethod, 
                               c.CustName, s.StaffName
                        FROM ORDERS o
                        JOIN CUSTOMER c ON o.CustId = c.CustId
                        LEFT JOIN STAFFS s ON o.StaffId = s.StaffId ";
                
                if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    $start = $_GET['start_date']; 
                    $end = $_GET['end_date'];
                    $sql .= " WHERE TRUNC(o.OrderDate) BETWEEN TO_DATE(:start_d, 'YYYY-MM-DD') AND TO_DATE(:end_d, 'YYYY-MM-DD') ";
                }
                $sql .= " ORDER BY o.OrderId DESC";
                
                $stmt = oci_parse($dbconn, $sql);
                if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    oci_bind_by_name($stmt, ":start_d", $start); 
                    oci_bind_by_name($stmt, ":end_d", $end);
                }
                oci_execute($stmt);

                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    // Logic Icon Payment
                    $pay = $row['PAYMENTMETHOD'];
                    $payBadge = "";
                    
                    if ($pay == 'QR') {
                        $payBadge = '<span class="badge bg-info text-dark rounded-pill"><i class="fas fa-qrcode me-1"></i> QR Pay</span>';
                    } elseif ($pay == 'CARD') {
                        $payBadge = '<span class="badge bg-warning text-dark rounded-pill"><i class="fas fa-credit-card me-1"></i> Card</span>';
                    } else {
                        $payBadge = '<span class="badge bg-success rounded-pill"><i class="fas fa-money-bill-wave me-1"></i> Cash</span>';
                    }

                    echo "<tr>";
                    echo "<td><strong>#" . $row['ORDERID'] . "</strong></td>";
                    echo "<td>" . date('d-m-Y h:i A', strtotime($row['ORDERDATE'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['CUSTNAME']) . "</td>";
                    echo "<td>" . $payBadge . "</td>";
                    echo "<td>" . htmlspecialchars($row['STAFFNAME'] ? $row['STAFFNAME'] : 'N/A') . "</td>";
                    echo "<td class='text-end fw-bold text-primary'>RM " . number_format($row['TOTALAMOUNT'], 2) . "</td>";
                    echo "<td class='text-center'>
                            <a href='order_details.php?id=".$row['ORDERID']."' class='btn btn-sm btn-light text-info fw-bold shadow-sm rounded-pill px-3'>
                                <i class='fas fa-eye me-1'></i> View
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
        var table = $('#tableOrders').DataTable({
            "dom": "rtip",
            "pageLength": 10,
            "columnDefs": [{ "orderable": false, "targets": [6] }], 
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ orders</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });
</script>
</body>
</html>