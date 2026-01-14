<?php
session_start();
require_once('db_conn.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ---------------------------------------------------------
    // LANGKAH 1: Semak dalam jadual STAFFS dahulu
    // ---------------------------------------------------------
    $sql_staff = "SELECT StaffId, StaffName, StaffRole FROM STAFFS 
                  WHERE StaffEmail = :email AND StaffPassword = :pass";

    $stid_staff = oci_parse($dbconn, $sql_staff);
    oci_bind_by_name($stid_staff, ":email", $email);
    oci_bind_by_name($stid_staff, ":pass", $password);
    oci_execute($stid_staff);
    
    $row_staff = oci_fetch_array($stid_staff, OCI_ASSOC);

    if ($row_staff) {
        // --- JIKA STAFF ATAU ADMIN ---
        $_SESSION['user_id'] = $row_staff['STAFFID'];
        $_SESSION['user_name'] = $row_staff['STAFFNAME'];
        $_SESSION['user_role'] = $row_staff['STAFFROLE']; // 'ADMIN' atau 'STAFF'

        if ($_SESSION['user_role'] == 'ADMIN') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
        exit();

    } else {
        // ---------------------------------------------------------
        // LANGKAH 2: Jika bukan Staff, semak jadual CUSTOMER pula
        // ---------------------------------------------------------
        // Nota: Jadual CUSTOMER tiada kolum 'Role', jadi kita set manual
        $sql_cust = "SELECT CustId, CustName FROM CUSTOMER 
                     WHERE CustEmail = :email AND CustPassword = :pass";

        $stid_cust = oci_parse($dbconn, $sql_cust);
        oci_bind_by_name($stid_cust, ":email", $email);
        oci_bind_by_name($stid_cust, ":pass", $password);
        oci_execute($stid_cust);

        $row_cust = oci_fetch_array($stid_cust, OCI_ASSOC);

        if ($row_cust) {
            // --- JIKA CUSTOMER ---
            $_SESSION['user_id'] = $row_cust['CUSTID'];
            $_SESSION['user_name'] = $row_cust['CUSTNAME'];
            $_SESSION['user_role'] = 'CUSTOMER'; // Set secara manual

            // Hantar Customer ke halaman utama kedai (bukan dashboard admin)
            header("Location: index.php"); 
            exit();
        } else {
            // ---------------------------------------------------------
            // LANGKAH 3: Jika dua-dua tiada, baru paparkan error
            // ---------------------------------------------------------
            header("Location: login.php?error=1");
            exit();
        }
        oci_free_statement($stid_cust);
    }
    
    oci_free_statement($stid_staff);
    oci_close($dbconn);  
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Abah FruitHub</title>
    <style>
        /* CSS UNTUK DESIGN YANG PROFESIONAL */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .login-container {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-container h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 24px;
        }

        .input-group {
            text-align: left;
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 600;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box; /* Penting supaya input tidak terkeluar */
            transition: border-color 0.3s;
        }

        .input-group input:focus {
            border-color: #9b59b6;
            outline: none;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #9b59b6;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background-color: #8e44ad;
        }

        .footer-text {
            margin-top: 1.5rem;
            font-size: 14px;
            color: #888;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Abah FruitHub Login</h2>
    
    <?php
    // Paparkan mesej jika login gagal
    if(isset($_GET['error'])) {
        echo '<div class="error-msg">E-mel atau Kata Laluan Salah!</div>';
    }
    ?>

    <form action="process_login.php" method="POST">
        <div class="input-group">
            <label for="email">E-mel Pekerja</label>
            <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
        </div>

        <div class="input-group">
            <label for="password">Kata Laluan</label>
            <input type="password" id="password" name="password" placeholder="Mesti 15 karakter" required>
        </div>

        <button type="submit" name="login" class="login-btn">MASUK SISTEM</button>
    </form>

    <div class="footer-text">
        &copy; 2026 Abah FruitHub Management System
    </div>
</div>

</body>
</html>