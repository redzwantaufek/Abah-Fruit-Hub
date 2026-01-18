<?php
session_start();
require_once('db_conn.php');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

// Query untuk mencari stok rendah DAN buah yang hampir tamat tempoh (dalam masa 7 hari)
$sql = "SELECT f.*, s.SupplierName 
        FROM FRUITS f 
        LEFT JOIN SUPPLIER s ON f.SupplierId = s.SupplierId 
        WHERE f.QuantityStock < 15 
        OR f.ExpireDate <= SYSDATE + 7
        ORDER BY f.ExpireDate ASC";
$stmt = oci_parse($dbconn, $sql);
oci_execute($stmt);
?>

<div class="container-fluid">
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold"><i class="fas fa-exclamation-circle text-danger me-2"></i>Inventory Alert Report</h3>
            <button onclick="window.print()" class="btn btn-outline-dark d-print-none"><i class="fas fa-print"></i> Print</button>
        </div>
        
        <div class="alert alert-info d-print-none">
            <i class="fas fa-info-circle me-2"></i> This report shows items with <strong>low stock (< 15)</strong> or <strong>expiring within 7 days</strong>.
        </div>

        <table class="table table-hover border">
            <thead class="table-warning">
                <tr>
                    <th>Fruit Name</th>
                    <th>Current Stock</th>
                    <th>Expiry Date</th>
                    <th>Supplier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($stmt, OCI_ASSOC)) { 
                    $is_low = ($row['QUANTITYSTOCK'] < 15);
                    $is_expiring = (strtotime($row['EXPIREDATE']) <= strtotime('+7 days'));
                ?>
                <tr>
                    <td><strong><?php echo $row['FRUITNAME']; ?></strong></td>
                    <td class="<?php echo $is_low ? 'text-danger fw-bold' : ''; ?>"><?php echo $row['QUANTITYSTOCK']; ?></td>
                    <td class="<?php echo $is_expiring ? 'text-danger fw-bold' : ''; ?>"><?php echo date('d/m/Y', strtotime($row['EXPIREDATE'])); ?></td>
                    <td><?php echo $row['SUPPLIERNAME']; ?></td>
                    <td>
                        <?php if($is_low) echo "<span class='badge bg-danger'>RESTOCK NEEDED</span> "; ?>
                        <?php if($is_expiring) echo "<span class='badge bg-warning text-dark'>EXPIRING SOON</span>"; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>