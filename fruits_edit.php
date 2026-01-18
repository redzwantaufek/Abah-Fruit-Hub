<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

$fid = $_GET['id'] ?? null;
if (!$fid) { header("Location: fruits.php"); exit(); }

$msg = "";

// Update Logic
if (isset($_POST['update_item'])) {
    $q_upd = "UPDATE FRUITS SET FruitName=:nm, FruitPrice=:pr, QuantityStock=:st, 
              Category=:cat, ExpireDate=TO_DATE(:exp, 'YYYY-MM-DD'), SupplierId=:sup WHERE FruitId=:fid";
    
    $stmt = oci_parse($dbconn, $q_upd);
    oci_bind_by_name($stmt, ":nm", $_POST['name']);
    oci_bind_by_name($stmt, ":pr", $_POST['price']);
    oci_bind_by_name($stmt, ":st", $_POST['stock']);
    oci_bind_by_name($stmt, ":cat", $_POST['category']);
    oci_bind_by_name($stmt, ":exp", $_POST['expire_date']);
    oci_bind_by_name($stmt, ":sup", $_POST['supplier_id']);
    oci_bind_by_name($stmt, ":fid", $fid);

    if (oci_execute($stmt)) {
        $msg = "Swal.fire('Updated!', 'Item details saved.', 'success').then(() => { window.location = 'fruits.php'; });";
    } else {
        $e = oci_error($stmt);
        $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
    }
}

// Fetch Data
$q_get = "SELECT * FROM FRUITS WHERE FruitId = :fid";
$s_get = oci_parse($dbconn, $q_get);
oci_bind_by_name($s_get, ":fid", $fid);
oci_execute($s_get);
$item = oci_fetch_array($s_get, OCI_ASSOC);

if (!$item) { echo "<script>alert('Item not found.'); window.location='fruits.php';</script>"; exit(); }
?>

<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-4" style="max-width: 600px;">
        <h4 class="mb-4 text-primary fw-bold"><i class="fas fa-edit me-2"></i>Edit Fruit</h4>
        
        <form method="POST">
            <div class="mb-3">
                <label class="small fw-bold">Fruit Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($item['FRUITNAME']); ?>" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Price (RM)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $item['FRUITPRICE']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $item['QUANTITYSTOCK']; ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <option value="LOCAL" <?php echo ($item['CATEGORY']=='LOCAL')?'selected':''; ?>>Local</option>
                        <option value="IMPORTED" <?php echo ($item['CATEGORY']=='IMPORTED')?'selected':''; ?>>Imported</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Expiry Date</label>
                    <input type="date" name="expire_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($item['EXPIREDATE'])); ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <?php
                    $s = oci_parse($dbconn, "SELECT SupplierId, SupplierName FROM SUPPLIER ORDER BY SupplierName");
                    oci_execute($s);
                    while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                        $sel = ($r['SUPPLIERID'] == $item['SUPPLIERID']) ? 'selected' : '';
                        echo "<option value='".$r['SUPPLIERID']."' $sel>".$r['SUPPLIERNAME']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update_item" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">
                    Save Changes
                </button>
                <a href="fruits.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>