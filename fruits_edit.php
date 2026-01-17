<?php
session_start();
require_once('db_conn.php');



include('includes/header.php');


// 1. Dapatkan Data Lama
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM FRUITS WHERE FruitId = :id";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":id", $id);
    oci_execute($stmt);
    $row = oci_fetch_array($stmt, OCI_ASSOC);
}

// 2. Proses Update Data
if (isset($_POST['update'])) {
    $fid = $_POST['fid'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // SQL Update
    $sql_upd = "UPDATE FRUITS SET FruitName=:nm, FruitPrice=:pr, QuantityStock=:st WHERE FruitId=:fid";
    $stmt_upd = oci_parse($dbconn, $sql_upd);
    
    oci_bind_by_name($stmt_upd, ":nm", $name);
    oci_bind_by_name($stmt_upd, ":pr", $price);
    oci_bind_by_name($stmt_upd, ":st", $stock);
    oci_bind_by_name($stmt_upd, ":fid", $fid);
    
    if(oci_execute($stmt_upd)) {
        echo "<script>alert('Data berjaya dikemaskini!'); window.location='fruits.php';</script>";
    } else {
        $e = oci_error($stmt_upd);
        echo "<script>alert('Gagal update: " . $e['message'] . "');</script>";
    }
}
?>

<div class="container mt-4">
    <div class="card card-custom p-4 mx-auto" style="max-width: 600px;">
        <h3>Kemaskini Buah</h3>
        <form method="POST">
            <input type="hidden" name="fid" value="<?php echo $row['FRUITID']; ?>">
            
            <div class="mb-3">
                <label>Nama Buah</label>
                <input type="text" name="name" class="form-control" value="<?php echo $row['FRUITNAME']; ?>" required>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label>Harga (RM)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $row['FRUITPRICE']; ?>" required>
                </div>
                <div class="col">
                    <label>Stok Semasa</label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $row['QUANTITYSTOCK']; ?>" required>
                </div>
            </div>
            <button type="submit" name="update" class="btn btn-primary w-100">Simpan Perubahan</button>
            <a href="fruits.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </form>
    </div>
</div>