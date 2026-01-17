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

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat = $_POST['category'];
    $exp = $_POST['expire_date']; // Format YYYY-MM-DD dari HTML
    $supp = $_POST['supplier'];

    // SQL INSERT dengan Sequence dan TO_DATE
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
    // --- POPUP BERJAYA (Centang Hijau) ---
    echo "
    <script>
        Swal.fire({
            title: 'Berjaya!',
            text: 'Data telah selamat disimpan.',
            icon: 'success', 
            confirmButtonColor: '#198754',
            confirmButtonText: 'OK'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'fruits.php'; // Redirect ke senarai lepas tekan OK
            }
        });
    </script>";
} else {
    // --- POPUP GAGAL (Pangkah Merah) ---
    $e = oci_error($stmt);
    // Kita guna json_encode supaya error message tak rosakkan koding JS
    $pesan_error = json_encode($e['message']); 
    
    echo "
    <script>
        Swal.fire({
            title: 'Ralat!',
            text: 'Gagal menyimpan data: ' + $pesan_error,
            icon: 'error',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Cuba Lagi'
        });
    </script>";
}
}
?>

<div class="container">
    <div class="card card-custom p-4 mt-4" style="max-width: 600px; margin: auto;">
        <h3 class="mb-3">Tambah Buah Baru</h3>
        
        <form method="POST">
            <div class="mb-3">
                <label>Nama Buah</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col">
                    <label>Harga (RM)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col">
                    <label>Stok Awal</label>
                    <input type="number" name="stock" class="form-control" required>
                </div>
            </div>
            <div class="mb-3 mt-3">
                <label>Kategori</label>
                <select name="category" class="form-select">
                    <option value="Tempatan">Tempatan</option>
                    <option value="Import">Import</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Tarikh Luput</label>
                <input type="date" name="expire_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Supplier</label>
                <select name="supplier" class="form-select">
                    <?php
                    // Tarik data Supplier untuk Dropdown
                    $s = oci_parse($dbconn, "SELECT SupplierId, SupplierName FROM SUPPLIER");
                    oci_execute($s);
                    while ($r = oci_fetch_array($s, OCI_ASSOC)) {
                        echo "<option value='".$r['SUPPLIERID']."'>".$r['SUPPLIERNAME']."</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="submit" class="btn btn-success w-100">Simpan Data</button>
            <a href="fruits.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </form>
    </div>
</div>