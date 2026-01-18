<?php
session_start();
require_once('db_conn.php');

// 1. SECURITY: Asalkan user sudah login, benarkan akses.
// Kita tak letak check 'ADMIN' supaya Staff pun boleh delete.
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); exit(); 
}

$id = $_GET['id'] ?? null;

if ($id) {
    // 2. PROSES DELETE
    $q = "DELETE FROM FRUITS WHERE FruitId = :id";
    $stmt = oci_parse($dbconn, $q);
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        // Jika berjaya, kembali ke senarai buah
        echo "<script>
            alert('Item deleted successfully.'); 
            window.location = 'fruits.php';
        </script>";
    } else {
        $e = oci_error($stmt);
        // Error selalunya sebab buah ini ada rekod jualan (Foreign Key)
        echo "<script>
            alert('Cannot delete: This fruit has sales records linked to it.'); 
            window.location = 'fruits.php';
        </script>";
    }
} else {
    // Jika tiada ID, balik ke page asal
    header("Location: fruits.php");
}
?>