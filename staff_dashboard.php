<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php');

$uid = $_SESSION['user_id'];

// Get Personal Sales Today
$q1 = "SELECT COUNT(*) as TXN, NVL(SUM(TotalAmount), 0) as TOTAL 
       FROM ORDERS 
       WHERE StaffId = :sid AND TRUNC(OrderDate) = TRUNC(SYSDATE)";
$s1 = oci_parse($dbconn, $q1);
oci_bind_by_name($s1, ":sid", $uid);
oci_execute($s1);
$my_stats = oci_fetch_assoc($s1);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-white text-shadow mb-0">Hello, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>! 👋</h3>
            <p class="text-white-50 small">Ready to make some sales today?</p>
        </div>
        <a href="sales_form.php" class="btn btn-warning fw-bold shadow px-4 py-2 rounded-pill">
            <i class="fas fa-cash-register me-2"></i> New Sales
        </a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                <div class="position-absolute end-0 bottom-0 p-3 opacity-25">
                    <i class="fas fa-wallet fa-5x text-success"></i>
                </div>
                <h6 class="fw-bold text-muted text-uppercase">My Sales Today</h6>
                <h1 class="display-4 fw-bold text-success">RM <?php echo number_format($my_stats['TOTAL'], 2); ?></h1>
                <p class="text-muted fw-bold mb-0"><?php echo $my_stats['TXN']; ?> Transactions processed</p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card p-4 h-100">
                <h6 class="fw-bold text-muted mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="sales_form.php" class="btn btn-primary fw-bold text-start p-3 shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i> Create New Order
                    </a>
                    <a href="orders.php" class="btn btn-info fw-bold text-start p-3 shadow-sm text-white">
                        <i class="fas fa-history me-2"></i> View My Sales History
                    </a>
                    <a href="fruits.php" class="btn btn-light fw-bold text-start p-3 shadow-sm border">
                        <i class="fas fa-search me-2"></i> Check Fruit Stock
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>