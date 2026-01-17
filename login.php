<?php
session_start();
require_once('db_conn.php'); 

// Logik Login (Berlaku dalam fail yang sama)
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Semak dalam jadual STAFFS
    $sql = "SELECT StaffId, StaffName, StaffRole FROM STAFFS 
            WHERE StaffEmail = :email AND StaffPassword = :pass";

    $stid = oci_parse($dbconn, $sql);

    oci_bind_by_name($stid, ":email", $email);
    oci_bind_by_name($stid, ":pass", $password);
    
    oci_execute($stid);
    
    $row = oci_fetch_array($stid, OCI_ASSOC);

    if ($row) {
        // --- LOGIN BERJAYA ---
        $_SESSION['user_id'] = $row['STAFFID'];
        $_SESSION['user_name'] = $row['STAFFNAME'];
        $_SESSION['user_role'] = $row['STAFFROLE']; 

        // Redirect ikut Role
        if ($_SESSION['user_role'] == 'ADMIN') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
        exit();

    } else {
        // --- LOGIN GAGAL ---
        $error_message = "Emel atau Kata Laluan tidak sah.";
    }
    
    oci_free_statement($stid);
    oci_close($dbconn);
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Abah FruitHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #198754; /* Hijau */
            color: white;
            text-align: center;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
            padding: 20px;
        }
        .btn-login {
            background-color: #198754;
            border: none;
        }
        .btn-login:hover {
            background-color: #146c43;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card card-login">
                <div class="card-header">
                    <h3><i class="fas fa-apple-alt me-2"></i>Abah FruitHub</h3>
                    <p class="mb-0 small">Sistem Pengurusan Buah</p>
                </div>
                
                <div class="card-body p-4">
                    
                    <?php if(isset($_GET['error'])) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> Emel atau Kata Laluan Salah!
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php } ?>
                    <form action="process_login.php" method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Emel</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="staff@email.com" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Kata Laluan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="login" class="btn btn-primary btn-login btn-lg">
                                Log Masuk <i class="fas fa-sign-in-alt ms-2"></i>
                            </button>
                        </div>

                    </form>
                </div>
                
                <div class="card-footer text-center py-3 bg-light" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <small class="text-muted">&copy; 2026 Abah FruitHub System</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>