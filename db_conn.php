<?php

/* php & Oracle DB connection file */

$username = "SYSTEM";
$password = "123";
$database = "localhost/FREEPDB1"; // Format: host/service_name

$dbconn = oci_connect($username, $password, $database);

if (!$dbconn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
} else {
    echo "ORACLE DATABASE CONNECTED SUCCESSFULLY!!!";
}
   
?>