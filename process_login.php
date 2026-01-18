<?php
session_start();
require_once('db_conn.php');

if (!isset($_POST['email']) || !isset($_POST['password'])) {
    header("Location: login.php"); exit();
}

$email = $_POST['email'];
$pass  = $_POST['password'];

// Check user
$q = "SELECT * FROM STAFFS WHERE StaffEmail = :em";
$stmt = oci_parse($dbconn, $q);
oci_bind_by_name($stmt, ":em", $email);
oci_execute($stmt);

$user = oci_fetch_array($stmt, OCI_ASSOC);

if ($user) {
    // Simple password check (In real app, use password_verify)
    if ($pass === $user['STAFFPASSWORD']) {
        $_SESSION['user_id'] = $user['STAFFID'];
        $_SESSION['user_name'] = $user['STAFFNAME'];
        $_SESSION['user_role'] = $user['STAFFROLE'];

        // Redirect based on role
        if ($user['STAFFROLE'] == 'ADMIN') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
    } else {
        header("Location: login.php?error=Incorrect password");
    }
} else {
    header("Location: login.php?error=User not found");
}
?>