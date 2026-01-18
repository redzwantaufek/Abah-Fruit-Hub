<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'ADMIN') header("Location: admin_dashboard.php");
    else header("Location: staff_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FruitHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            height: 100vh;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass-login {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%; max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .form-control { border-radius: 10px; padding: 12px; border: 1px solid #ddd; }
        .btn-login {
            border-radius: 10px; padding: 12px; font-weight: bold;
            background: linear-gradient(45deg, #11998e, #38ef7d); border: none;
            transition: transform 0.2s;
        }
        .btn-login:hover { transform: scale(1.02); }
        /* Style untuk butang mata */
        .btn-eye { cursor: pointer; background: white; border: 1px solid #ddd; border-left: 0; }
        .btn-eye:hover { background: #f8f9fa; }
    </style>
</head>
<body>

<div class="glass-login text-center">
    <div class="mb-4">
        <i class="fas fa-apple-alt fa-3x text-success mb-2"></i>
        <h3 class="fw-bold text-dark">FruitHub System</h3>
        <p class="text-muted small">Please sign in to continue</p>
    </div>

    <?php if(isset($_GET['error'])) { ?>
        <div class="alert alert-danger py-2 small rounded-pill">
            <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php } ?>

    <form action="process_login.php" method="POST">
        <div class="mb-3 text-start">
            <label class="fw-bold small ms-1">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                <input type="email" name="email" class="form-control border-start-0" placeholder="staff@fruithub.com" required>
            </div>
        </div>

        <div class="mb-4 text-start">
            <label class="fw-bold small ms-1">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                <input type="password" name="password" id="passInput" class="form-control border-start-0 border-end-0" placeholder="••••••••" required>
                <span class="input-group-text btn-eye" onclick="togglePass()">
                    <i class="fas fa-eye text-muted" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-login shadow">
            Sign In
        </button>
    </form>
    
    <div class="mt-4 text-muted small">
        &copy; 2026 FruitHub Management
    </div>
</div>

<script>
function togglePass() {
    var x = document.getElementById("passInput");
    var icon = document.getElementById("eyeIcon");
    
    if (x.type === "password") {
        x.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        x.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>