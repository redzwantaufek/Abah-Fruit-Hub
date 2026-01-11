<?php
// Ganti maklumat di bawah mengikut tetapan Oracle 23ai anda
$username = "SYSTEM";
$password = "123";
$database = "localhost/FREEPDB1"; // Format: host/service_name

$conn = oci_connect($username, $password, $database);

if (!$conn) {
    $e = oci_error();
    echo "Gagal bersambung ke Oracle 23ai: " . $e['message'];
} else {
    echo "<h3>Berjaya!</h3>";
    echo "Frontend anda (PHP) kini sudah bersambung dengan Backend (Oracle 23ai Free).";
    
    // Cuba ambil versi database
    $s = oci_parse($conn, "SELECT banner FROM v$version");
    oci_execute($s);
    while (($row = oci_fetch_array($s, OCI_ASSOC+OCI_RETURN_NULLS)) != false) {
        foreach ($row as $item) {
            echo "<p>Versi: " . $item . "</p>";
        }
    }
}

oci_close($conn);
?>