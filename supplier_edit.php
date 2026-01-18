<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

include('includes/header.php');

// 1. Get Current Data
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM SUPPLIER WHERE SupplierId = :id";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
    
    if(!$row) {
        echo "<script>alert('Supplier not found!'); window.location='supplier.php';</script>";
        exit();
    }
}

// 2. Update Process
if (isset($_POST['update'])) {
    $sid = $_POST['sid'];
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $phone = $_POST['phone'];
    $type = $_POST['type'];

    $sql_upd = "UPDATE SUPPLIER SET SupplierName=:nm, SupplierContact=:ct, SupplierPhone=:ph, SupplierType=:tp 
                WHERE SupplierId=:sid";
    
    $stmt = oci_parse($dbconn, $sql_upd);
    oci_bind_by_name($stmt, ":nm", $name);
    oci_bind_by_name($stmt, ":ct", $contact);
    oci_bind_by_name($stmt, ":ph", $phone);
    oci_bind_by_name($stmt, ":tp", $type);
    oci_bind_by_name($stmt, ":sid", $sid);

    if (oci_execute($stmt)) {
        echo "<script>
            Swal.fire('Success!', 'Supplier details updated.', 'success').then(() => {
                window.location = 'supplier.php';
            });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "<script>Swal.fire('Error', '" . $e['message'] . "', 'error');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="glass-card mx-auto" style="max-width: 600px;">
        <h3 class="mb-3 text-primary fw-bold">✏️ Edit Supplier</h3>
        
        <form method="POST">
            <input type="hidden" name="sid" value="<?php echo $row['SUPPLIERID']; ?>">
            
            <div class="mb-3">
                <label>Company Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $row['SUPPLIERNAME']; ?>" required>
            </div>

            <div class="row mb-3">
                <div class="col">
                    <label>Contact Person</label>
                    <input type="text" name="contact" class="form-control" value="<?php echo $row['SUPPLIERCONTACT']; ?>" required>
                </div>
                <div class="col">
                    <label>Phone No.</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $row['SUPPLIERPHONE']; ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Supplier Type</label>
                <select name="type" class="form-select">
                    <option value="Fruits" <?php if($row['SUPPLIERTYPE']=='Fruits') echo 'selected'; ?>>Fruits</option>
                    <option value="Packaging" <?php if($row['SUPPLIERTYPE']=='Packaging') echo 'selected'; ?>>Packaging</option>
                    <option value="Logistics" <?php if($row['SUPPLIERTYPE']=='Logistics') echo 'selected'; ?>>Logistics</option>
                </select>
            </div>

            <button type="submit" name="update" class="btn btn-primary w-100 fw-bold">Save Changes</button>
            <a href="supplier.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>