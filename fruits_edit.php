<?php
session_start();
require_once('db_conn.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php');

$fid = $_GET['id'] ?? null;
if (!$fid) { header("Location: fruits.php"); exit(); }

$msg = "";

// 1. DAPATKAN DATA LAMA
$q_get = "SELECT * FROM FRUITS WHERE FruitId = :fid";
$s_get = oci_parse($dbconn, $q_get);
oci_bind_by_name($s_get, ":fid", $fid);
oci_execute($s_get);
$item = oci_fetch_array($s_get, OCI_ASSOC);

if (!$item) { echo "<script>alert('Item not found.'); window.location='fruits.php';</script>"; exit(); }

// SAFETY FIX: Pastikan key IMAGEURL wujud walaupun database return huruf kecil/besar
// Kita check semua variasi ejaan yang mungkin
$current_db_img = $item['IMAGEURL'] ?? $item['ImageURL'] ?? $item['imageurl'] ?? '';

// 2. PROSES UPDATE
if (isset($_POST['update_item'])) {
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat   = $_POST['category'];
    $exp   = $_POST['expire_date'];
    $supp  = $_POST['supplier_id'];
    
    // Default: Guna gambar lama (yang kita dah fix kat atas tadi)
    $final_img = $current_db_img; 
    $proceed = true;

    // JIKA USER UPLOAD GAMBAR BARU
    if (isset($_FILES['fruit_img']) && $_FILES['fruit_img']['error'] == 0) {
        $fileName = $_FILES['fruit_img']['name'];
        $fileSize = $_FILES['fruit_img']['size'];
        $tmpName  = $_FILES['fruit_img']['tmp_name'];
        
        $validImageExtension = ['jpg', 'jpeg', 'png'];
        $imageExtension = explode('.', $fileName);
        $imageExtension = strtolower(end($imageExtension));

        // 1. Validation Format
        if (!in_array($imageExtension, $validImageExtension)) {
            $msg = "Swal.fire('Error', 'Invalid Format (JPG/PNG only)', 'error');";
            $proceed = false;
        }
        // 2. Validation Size (Max 2MB)
        else if ($fileSize > 2000000) { 
            $msg = "Swal.fire('Error', 'Image too large (Max 2MB)', 'error');";
            $proceed = false;
        }
        // 3. Upload File Baru
        else {
            $newImageName = time() . "." . $imageExtension;
            $target = "assets/img/" . $newImageName;
            
            if (move_uploaded_file($tmpName, $target)) {
                $final_img = $newImageName; // Tukar variable ke nama baru
            } else {
                $msg = "Swal.fire('Error', 'Failed to upload image.', 'error');";
                $proceed = false;
            }
        }
    }

    // Jalankan UPDATE SQL hanya jika validation lulus
    if ($proceed) {
        $q_upd = "UPDATE FRUITS SET FruitName=:nm, FruitPrice=:pr, QuantityStock=:st, 
                  Category=:cat, ExpireDate=TO_DATE(:exp, 'YYYY-MM-DD'), SupplierId=:sup, ImageURL=:img 
                  WHERE FruitId=:fid";
        
        $stmt = oci_parse($dbconn, $q_upd);
        oci_bind_by_name($stmt, ":nm", $name);
        oci_bind_by_name($stmt, ":pr", $price);
        oci_bind_by_name($stmt, ":st", $stock);
        oci_bind_by_name($stmt, ":cat", $cat);
        oci_bind_by_name($stmt, ":exp", $exp);
        oci_bind_by_name($stmt, ":sup", $supp);
        oci_bind_by_name($stmt, ":img", $final_img);
        oci_bind_by_name($stmt, ":fid", $fid);

        if (oci_execute($stmt)) {
            $msg = "Swal.fire('Updated!', 'Item details updated.', 'success').then(() => { window.location = 'fruits.php'; });";
            // Kemaskini variable tempatan untuk paparan preview serta-merta
            $current_db_img = $final_img; 
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
        <h4 class="mb-4 text-primary fw-bold"><i class="fas fa-edit me-2"></i>Edit Fruit</h4>
        
        <form method="POST" enctype="multipart/form-data">
            
            <div class="mb-4 text-center">
                <div class="bg-light border rounded d-inline-block p-3 mb-2" style="width: 150px; height: 150px; overflow: hidden;">
                    <?php 
                        // Logic nak tunjuk gambar lama atau placeholder
                        $show_img = "https://via.placeholder.com/150?text=No+Image";
                        
                        // Guna variable $current_db_img yang dah kita 'fix' kat atas
                        if (!empty($current_db_img) && file_exists("assets/img/" . $current_db_img)) {
                            $show_img = "assets/img/" . $current_db_img;
                        }
                    ?>
                    <img id="preview" src="<?php echo $show_img; ?>" class="w-100 h-100 object-fit-cover">
                </div>
                <input type="file" name="fruit_img" class="form-control form-control-sm mt-2" accept="image/*" onchange="previewImage(this)">
                <small class="text-muted d-block mt-1">Leave empty to keep current image (Max 2MB)</small>
            </div>

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

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        if(input.files[0].size > 2097152) {
             alert("File is too big! Max 2MB.");
             input.value = "";
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