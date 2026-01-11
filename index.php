<?php
// 1. Maklumat sambungan
$username = "SYSTEM"; 
$password = "123"; // Ganti dengan password anda
$database = "localhost/FREEPDB1";

// 2. Buat sambungan
$conn = oci_connect($username, $password, $database);

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

echo "<h1>Selamat Datang ke Fruit Stall</h1>";

// 3. Query data dari Oracle
$query = "SELECT * FROM buah";
$stid = oci_parse($conn, $query);
oci_execute($stid);

// 4. Paparkan dalam bentuk senarai
echo "<ul>";
while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
    echo "<li>" . $row['NAMA_BUAH'] . " - RM" . $row['HARGA'] . " (Stok: " . $row['STOK'] . ")</li>";
}
echo "</ul>";

oci_free_statement($stid);
oci_close($conn);
?>