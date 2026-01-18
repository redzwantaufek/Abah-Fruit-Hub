<?php

/* php & Oracle DB connection file */

$username = "ict502";
$password = "system";
$database = "localhost/FREEPDB1"; // Format: host/service_name

$dbconn = oci_connect($username, $password, $database);

if (!$dbconn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
} else {
    // Connection successful
}
   
?>