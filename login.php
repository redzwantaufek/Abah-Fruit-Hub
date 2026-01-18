<?php
session_start();
require_once('db_conn.php'); 

// Login Logic
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT StaffId, StaffName, StaffRole FROM STAFFS 
            WHERE StaffEmail = :email AND StaffPassword = :pass";

    $stid = oci_parse($dbconn, $sql);
    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":pass", $password);
    oci_execute($stid);
    
    $row = oci_fetch_array($stid, OCI_ASSOC);

    if ($row) {
        $_SESSION['user_id'] = $row['STAFFID'];
        $_SESSION['user_name'] = $row['STAFFNAME'];
        $_SESSION['user_role'] = $row['STAFFROLE']; 

        if ($_SESSION['user_role'] == 'ADMIN') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Abah FruitHub</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Animasi Background Seragam dengan Dashboard */
        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glassmorphism Card Style */
        .glass-login-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            color: white;
            text-align: center;
        }

        .brand-logo {
            font-size: 3rem;
            margin-bottom: 10px;
            color: #fff;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.2));
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px;
            padding: 12px;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px 0 0 10px;
        }

        .btn-login {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            border: none;
            color: #000;
            font-weight: bold;
            padding: 12px;
            border-radius: 10px;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-login:hover {
            transform: scale(1.05);
            background: #fff;
            color: #198754;
        }

        .text-shadow {
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .toggle-pass {
            cursor: pointer;
            border-radius: 0 10px 10px 0 !important;
        }
    </style>
</head>
<body>

<div class="glass-login-card">
    <div class="brand-logo">
        <i class="fas fa-apple-alt"></i>
    </div>
    <h2 class="fw-bold text-shadow">FruitHub</h2>
    <p class="mb-4 text-shadow opacity-75">Management System Login</p>

    <?php if(isset($_GET['error'])) { ?>
        <div class="alert alert-danger border-0 small py-2 mb-3" role="alert" style="background: rgba(255,0,0,0.6); color: white;">
            <i class="fas fa-exclamation-circle me-1"></i> Invalid Email or Password!
        </div>
    <?php } ?>

    <form action="" method="POST">
        <div class="mb-3 text-start">
            <label class="small ms-1 mb-1">Email Address</label>
            <div class="input-group shadow-sm">
                <span class="input-group-text"><i class="fas fa-envelope text-success"></i></span>
                <input type="email" name="email" class="form-control" placeholder="staff@fruithub.com" required>
            </div>
        </div>

        <div class="mb-4 text-start">
            <label class="small ms-1 mb-1">Password</label>
            <div class="input-group shadow-sm">
                <span class="input-group-text"><i class="fas fa-lock text-success"></i></span>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                <span class="input-group-text toggle-pass" onclick="togglePassword()">
                    <i class="fas fa-eye text-muted" id="toggleIcon"></i>
                </span>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" name="login" class="btn btn-login btn-lg">
                SIGN IN <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>

    <div class="mt-4 pt-2 border-top border-light opacity-50">
        <small>&copy; 2026 Abah FruitHub System</small>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordField.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    }
</script>

<script src="assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>