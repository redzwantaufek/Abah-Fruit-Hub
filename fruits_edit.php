<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

if (!isset($_GET['id'])) { header("Location: fruits.php"); exit(); }
$fid = $_GET['id'];

// Get Existing Data
$sql_get = "SELECT * FROM FRUITS WHERE FruitId = :fid";
$stmt_get = oci_parse($dbconn, $sql_get);
oci_bind_by_name($stmt_get, ":fid", $fid);
oci_execute($stmt_get);
$row = oci_fetch_array($stmt_get, OCI_ASSOC);

if (!$row) { echo "<script>alert('Fruit not found!'); window.location='fruits.php';</script>"; exit(); }

// Update Logic
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat = $_POST['category'];
    $exp = $_POST['expire_date']; // Format: YYYY-MM-DD from input type=date
    $supp = $_POST['supplier_id'];

    $sql_upd = "UPDATE FRUITS SET FruitName=:nm, FruitPrice=:pr, QuantityStock=:st, 
                Category=:cat, ExpireDate=TO_DATE(:exp, 'YYYY-MM-DD'), SupplierId=:sup WHERE FruitId=:fid";
    
    $stmt_upd = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt_upd, ":nm", $name);
    oci_bind_by_name($stmt_upd, ":pr", $price);
    oci_bind_by_name($stmt_upd, ":st", $stock);
    oci_bind_by_name($stmt_upd, ":cat", $cat);
    oci_bind_by_name($stmt_upd, ":exp", $exp);
    oci_bind_by_name($stmt_upd, ":sup", $supp);
    oci_bind_by_name($stmt_upd, ":fid", $fid);

    if (oci_execute($stmt_upd)) {
        echo "<script>Swal.fire('Updated!', 'Fruit details updated successfully.', 'success').then(() => { window.location = 'fruits.php'; });</script>";
    } else {
        $e = oci_error($stmt_upd);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-4" style="max-width: 600px;">
        <h3 class="mb-4 text-primary fw-bold"><i class="fas fa-edit me-2"></i>Edit Fruit</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label class="fw-bold">Fruit Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['FRUITNAME']); ?>" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Price (RM)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $row['FRUITPRICE']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Current Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $row['QUANTITYSTOCK']; ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <option value="LOCAL" <?php if($row['CATEGORY']=='LOCAL') echo 'selected'; ?>>Local</option>
                        <option value="IMPORTED" <?php if($row['CATEGORY']=='IMPORTED') echo 'selected'; ?>>Imported</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Expiry Date</label>
                    <input type="date" name="expire_date" class="form-control" value="<?php echo date('Y-m-d', strtotime($row['EXPIREDATE'])); ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <?php
                    $s = oci_parse($dbconn, "SELECT SupplierId, SupplierName FROM SUPPLIER ORDER BY SupplierName");
                    oci_execute($s);
                    while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                        $selected = ($r['SUPPLIERID'] == $row['SUPPLIERID']) ? 'selected' : '';
                        echo "<option value='".$r['SUPPLIERID']."' $selected>".$r['SUPPLIERNAME']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update" class="btn btn-primary fw-bold flex-grow-1 shadow">
                    <i class="fas fa-save me-2"></i> Update Changes
                </button>
                <a href="fruits.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>