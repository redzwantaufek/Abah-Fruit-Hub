<?php
session_start();
require_once('db_conn.php');

// --- SECURITY CHECK: LOGIN REQUIRED ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// --------------------------------------

include('includes/header.php'); 

// Kira Jualan Staff Ini Hari Ini
$my_id = $_SESSION['user_id'];
$sql_sale = "SELECT COUNT(*) AS TOTAL_ORDER, SUM(TotalAmount) AS TOTAL_RM 
             FROM ORDERS 
             WHERE StaffId = :sid AND TRUNC(OrderDate) = TRUNC(SYSDATE)";
$stmt = oci_parse($dbconn, $sql_sale);
oci_bind_by_name($stmt, ":sid", $my_id);
oci_execute($stmt);
$stat = oci_fetch_array($stmt, OCI_ASSOC);
?>

<div class="container-fluid">
    <div class="alert alert-primary">
        <h4><i class="fas fa-user-tag"></i> Dashboard Staff</h4>
        <p>Selamat Datang, <strong><?php echo $_SESSION['user_name']; ?></strong>. Selamat bekerja!</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white p-3">
                <h5>Jualan Saya Hari Ini</h5>
                <h3>RM <?php echo number_format($stat['TOTAL_RM'] ?? 0, 2); ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-white p-3">
                <h5>Bilangan Resit Hari Ini</h5>
                <h3><?php echo $stat['TOTAL_ORDER']; ?> Transaksi</h3>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Tindakan Pantas</h5>
    <div class="row">
        <div class="col-md-4">
            <a href="sales_form.php" class="text-decoration-none">
                <div class="card card-custom p-4 text-center hover-shadow border-primary">
                    <i class="fas fa-cash-register fa-3x text-primary mb-3"></i>
                    <h4>Buat Jualan Baru</h4>
                    <p class="text-muted">Masuk order pelanggan & tolak stok</p>
                </div>
            </a>
        </div>
        
        <div class="col-md-4">
            <a href="customer_add.php" class="text-decoration-none">
                <div class="card card-custom p-4 text-center hover-shadow">
                    <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                    <h4>Daftar Pelanggan</h4>
                    <p class="text-muted">Jika pelanggan baru datang kedai</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="fruits.php" class="text-decoration-none">
                <div class="card card-custom p-4 text-center hover-shadow">
                    <i class="fas fa-boxes fa-3x text-warning mb-3"></i>
                    <h4>Semak Stok Buah</h4>
                    <p class="text-muted">Lihat baki buah dalam kedai</p>
                </div>
            </a>
        </div>
    </div>
</div>
</body>
</html>