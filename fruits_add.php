<?php
session_start();
require_once('db_conn.php');

// Security Check: Hanya Admin boleh masuk
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { 
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
    
    // --- LOGIC GAMBAR (DENGAN VALIDATION) ---
    $img_name = ""; // Default kosong jika tiada gambar
    $proceed = true; // Flag untuk check boleh teruskan atau tidak

    if (isset($_FILES['fruit_img']) && $_FILES['fruit_img']['error'] == 0) {
        $fileName = $_FILES['fruit_img']['name'];
        $fileSize = $_FILES['fruit_img']['size'];
        $tmpName  = $_FILES['fruit_img']['tmp_name'];
        
        $validImageExtension = ['jpg', 'jpeg', 'png'];
        $imageExtension = explode('.', $fileName);
        $imageExtension = strtolower(end($imageExtension));
        
        // 1. Check Format (Hanya JPG/PNG)
        if (!in_array($imageExtension, $validImageExtension)) {
            $msg = "Swal.fire('Error', 'Invalid Image Format. Use JPG or PNG only.', 'error');";
            $proceed = false;
        }
        // 2. Check Saiz (Max 2MB = 2,000,000 bytes)
        else if ($fileSize > 2000000) {
            $msg = "Swal.fire('Error', 'Image is too large. Maximum size is 2MB.', 'error');";
            $proceed = false;
        }
        // 3. Jika lulus, upload!
        else {
            $newImageName = time() . "." . $imageExtension;
            $target = "assets/img/" . $newImageName;
            
            if (move_uploaded_file($tmpName, $target)) {
                $img_name = $newImageName;
            } else {
                $msg = "Swal.fire('Error', 'Failed to upload image to folder.', 'error');";
                $proceed = false;
            }
        }
    }

    // Hanya masukkan ke database jika tiada error validation ($proceed == true)
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
        <h4 class="mb-4 text-primary fw-bold"><i class="fas fa-camera me-2"></i>New Item with Image</h4>
        
        <form method="POST" enctype="multipart/form-data">
            
            <div class="mb-4 text-center">
                <div class="bg-light border rounded d-inline-block p-3 mb-2" style="width: 150px; height: 150px; overflow: hidden;">
                    <img id="preview" src="https://via.placeholder.com/150?text=No+Image" class="w-100 h-100 object-fit-cover">
                </div>
                <input type="file" name="fruit_img" class="form-control form-control-sm mt-2" accept="image/*" onchange="previewImage(this)">
                <small class="text-muted d-block mt-1">Format: JPG/PNG • Max: 2MB</small>
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
                <button type="submit" name="save_item" class="btn btn-primary fw-bold flex-grow-1 shadow-sm">
                    Save Item
                </button>
                <a href="fruits.php" class="btn btn-secondary fw-bold shadow-sm">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        // Simple client-side size check alert (optional UX improvement)
        if(input.files[0].size > 2097152) {
             alert("File is too big! Max 2MB.");
             input.value = ""; // Clear input
             return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>