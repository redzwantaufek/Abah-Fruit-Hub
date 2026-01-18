<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat = $_POST['category'];
    $exp = $_POST['expire_date'];
    $supp = $_POST['supplier_id'];

    $sql = "INSERT INTO FRUITS (FruitId, FruitName, FruitPrice, QuantityStock, Category, ExpireDate, SupplierId) 
            VALUES (fruit_id_seq.NEXTVAL, :nm, :pr, :st, :cat, TO_DATE(:exp, 'YYYY-MM-DD'), :sup)";
    
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":pr", $price);
    oci_bind_by_name($stmt, ":st", $stock);
    oci_bind_by_name($stmt, ":cat", $cat);
    oci_bind_by_name($stmt, ":exp", $exp);
    oci_bind_by_name($stmt, ":sup", $supp);

    if (oci_execute($stmt)) {
        echo "<script>Swal.fire('Success!', 'New fruit added successfully.', 'success').then(() => { window.location = 'fruits.php'; });</script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto p-4" style="max-width: 600px;">
        <h3 class="mb-4 text-primary fw-bold"><i class="fas fa-apple-alt me-2"></i>Add New Fruit</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label class="fw-bold">Fruit Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Red Apple">
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Price (RM)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Initial Stock</label>
                    <input type="number" name="stock" class="form-control" required placeholder="0">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <option value="LOCAL">Local</option>
                        <option value="IMPORTED">Imported</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Expiry Date</label>
                    <input type="date" name="expire_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="fw-bold">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <?php
                    $s = oci_parse($dbconn, "SELECT SupplierId, SupplierName FROM SUPPLIER ORDER BY SupplierName");
                    oci_execute($s);
                    while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                        echo "<option value='".$r['SUPPLIERID']."'>".$r['SUPPLIERNAME']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="submit" class="btn btn-success fw-bold flex-grow-1 shadow">
                    <i class="fas fa-save me-2"></i> Save Item
                </button>
                <a href="fruits.php" class="btn btn-secondary fw-bold shadow">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>