<?php
session_start();
require_once('db_conn.php');

// 1. SECURITY: Hanya ADMIN boleh delete staff
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { 
    header("Location: login.php"); exit(); 
}

$id = $_GET['id'] ?? null;

// Halang padam diri sendiri
if ($id == $_SESSION['user_id']) {
    echo "<script>alert('Cannot delete your own account!'); window.location='staff.php';</script>";
    exit();
}

if ($id) {
    // 2. PROSES DELETE STAFF
    $q = "DELETE FROM STAFFS WHERE StaffId = :id";
    $stmt = oci_parse($dbconn, $q);
    oci_bind_by_name($stmt, ":id", $id);

    if (oci_execute($stmt)) {
        header("Location: staff.php?msg=deleted");
    } else {
        $e = oci_error($stmt);
        echo "<script>alert('Error deleting staff: " . $e['message'] . "'); window.location='staff.php';</script>";
    }
} else {
    header("Location: staff.php");
}
?>