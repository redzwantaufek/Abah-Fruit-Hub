<?php
session_start();
// Memanggil fail sambungan pangkalan data [cite: 10, 12]
require_once('db_conn.php'); 

if (isset($_POST['login'])) {
    // Mengambil data dari borang login
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL Query untuk menyemak staf berdasarkan Email dan Password [cite: 10]
    // Menggunakan bind variables (:email, :pass) untuk keselamatan data
    $sql = "SELECT StaffId, StaffName, StaffRole FROM STAFFS 
            WHERE StaffEmail = :email AND StaffPassword = :pass";

    $stid = oci_parse($conn, $sql);

    // Mengikat pembolehubah PHP ke dalam SQL Oracle
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":pass", $password);

    // Menjalankan arahan SQL
    oci_execute($stid);

    // Mengambil satu baris hasil jualan jika wujud
    $row = oci_fetch_array($stid, OCI_ASSOC);

    if ($row) {
        // Jika data dijumpai, simpan maklumat ke dalam SESSION
        $_SESSION['user_id'] = $row['STAFFID'];
        $_SESSION['user_name'] = $row['STAFFNAME'];
        $_SESSION['user_role'] = $row['STAFFROLE'];

        // Menentukan hala tuju berdasarkan Role (Admin atau Staff) 
        if ($_SESSION['user_role'] == 'ADMIN') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
        exit();
    } else {
        // Jika gagal, hantar kembali ke login.php dengan mesej ralat
        header("Location: login.php?error=1");
        exit();
    }

    // Menutup kenyataan dan sambungan
    oci_free_statement($stid);
    oci_close($conn);
} else {
    // Jika akses fail tanpa tekan butang login
    header("Location: login.php");
    exit();
}
?>