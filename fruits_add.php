<?php
session_start();
require_once('db_conn.php');

// 1. SECURITY UPDATE:
// Kita HANYA check jika user sudah login. Kita BUANG check 'ADMIN'.
// Staff pun boleh lepas sekarang.
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); exit(); 
}

include('includes/header.php');

$msg = "";

if (isset($_POST['save_item'])) {
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat   = $_POST['category'];
    $exp   = $_POST['expire_date'];
    $supp  = $_POST['supplier_id'];
    
    // Logic Gambar
    $img_name = ""; 
    $proceed = true;

    if (isset($_FILES['fruit_img']) && $_FILES['fruit_img']['error'] == 0) {
        $fileName = $_FILES['fruit_img']['name'];
        $fileSize = $_FILES['fruit_img']['size'];
        $tmpName  = $_FILES['fruit_img']['tmp_name'];
        
        $validImageExtension = ['jpg', 'jpeg', 'png'];
        $imageExtension = explode('.', $fileName);
        $imageExtension = strtolower(end($imageExtension));
        
        if (!in_array($imageExtension, $validImageExtension)) {
            $msg = "Swal.fire('Error', 'Invalid Image Format (JPG/PNG only).', 'error');";
            $proceed = false;
        }
        else if ($fileSize > 2000000) {
            $msg = "Swal.fire('Error', 'Image too large (Max 2MB).', 'error');";
            $proceed = false;
        }
        else {
            $newImageName = time() . "." . $imageExtension;
            $target = "assets/img/" . $newImageName;
            
            if (move_uploaded_file($tmpName, $target)) {
                $img_name = $newImageName;
            } else {
                $msg = "Swal.fire('Error', 'Failed to upload image.', 'error');";
                $proceed = false;
            }
        }
    }

    if ($proceed) {
        $q = "INSERT INTO FRUITS (FruitId, FruitName, FruitPrice, QuantityStock, Category, ExpireDate, SupplierId, ImageURL) 
              VALUES (fruit_id_seq.NEXTVAL, :nm, :pr, :st, :cat, TO_DATE(:exp, 'YYYY-MM-DD'), :sup, :img)";
        
        $stmt = oci_parse($dbconn, $q);
        oci_bind_by_name($stmt, ":nm", $name);
        oci_bind_by_name($stmt, ":pr", $price);
        oci_bind_by_name($stmt, ":st", $stock);
        oci_bind_by_name($stmt, ":cat", $cat);
        oci_bind_by_name($stmt, ":exp", $exp);
        oci_bind_by_name($stmt, ":sup", $supp);
        oci_bind_by_name($stmt, ":img", $img_name);

        if (oci_execute($stmt)) {
            $msg = "Swal.fire('Success', 'Item added successfully.', 'success').then(() => { window.location = 'fruits.php'; });";
        } else {
            $e = oci_error($stmt);
            $msg = "Swal.fire('Error', '" . $e['message'] . "', 'error');";
        }
    }
}
?>
<script><?php echo $msg; ?></script>

<div class="container mt-4">
    <div class="glass-card mx-auto p-4" style="max-width: 600px;">
        <h4 class="mb-4 text-primary fw-bold"><i class="fas fa-camera me-2"></i>Add New Fruit</h4>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4 text-center">
                <div class="bg-light border rounded d-inline-block p-3 mb-2" style="width: 150px; height: 150px; overflow: hidden;">
                    <img id="preview" src="https://via.placeholder.com/150?text=No+Image" class="w-100 h-100 object-fit-cover">
                </div>
                <input type="file" name="fruit_img" class="form-control form-control-sm mt-2" accept="image/*" onchange="previewImage(this)">
                <small class="text-muted">Max: 2MB (JPG/PNG)</small>
            </div>

            <div class="mb-3">
                <label class="small fw-bold">Fruit Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Price (RM)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Initial Stock</label>
                    <input type="number" name="stock" class="form-control" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="small fw-bold">Category</label>
                    <select name="category" class="form-select">
                        <option value="LOCAL">Local</option>
                        <option value="IMPORTED">Imported</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="small fw-bold">Expiry Date</label>
                    <input type="date" name="expire_date" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="small fw-bold">Supplier</label>
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
                <button type="submit" name="save_item" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">Save Item</button>
                <a href="fruits.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        if(input.files[0].size > 2097152) { alert("File too big!"); input.value = ""; return; }
        var reader = new FileReader();
        reader.onload = function (e) { document.getElementById('preview').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>