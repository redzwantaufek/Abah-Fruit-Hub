<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fruit Stall Management System</title>
    
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/sweetalert2.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/chart.js"></script>

    <style>
        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .sidebar {
            min-height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            color: white;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar a { color: #ddd; text-decoration: none; display: block; padding: 12px; border-radius: 8px; transition: 0.3s; }
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.2); color: white; transform: translateX(5px); }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 20px;
            margin-bottom: 20px;
        }
        .content { padding: 20px; }
        
        .btn-jualan { 
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            color: #000 !important; font-weight: bold; border-radius: 8px; margin: 10px 0; text-align: center; 
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        }
        .btn-jualan:hover { transform: scale(1.05); }

        @media print {
            .sidebar, .btn, .navbar, form { display: none !important; }
            .glass-card { border: 1px solid #000; box-shadow: none; width: 100%; position: absolute; top: 0; left: 0; }
            body { background: white !important; animation: none; }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-3" style="width: 250px;">
        <h4 class="text-center mb-4"><i class="fas fa-apple-alt"></i> FruitHub</h4>
        
        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'ADMIN') { ?>
            <a href="admin_dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <?php } else { ?>
            <a href="staff_dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <?php } ?>

        <a href="sales_form.php" class="btn-jualan"><i class="fas fa-cash-register me-2"></i> New Sale</a>
        <a href="fruits.php"><i class="fas fa-boxes me-2"></i> Fruit Stock</a>
        <a href="orders.php"><i class="fas fa-receipt me-2"></i> Sales History</a>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'ADMIN') { ?>
            <hr class="bg-light">
            <small class="text-uppercase text-muted ms-2" style="font-size: 0.7rem;">Admin Area</small>
            <a href="staff.php"><i class="fas fa-users me-2"></i> Staff Management</a>
            <a href="customer.php"><i class="fas fa-user-friends me-2"></i> Customers</a>
            <a href="supplier.php"><i class="fas fa-truck me-2"></i> Suppliers</a>
        <?php } ?>

        <hr class="bg-light">
        <a href="profile.php"><i class="fas fa-user-circle me-2"></i> My Profile</a>
        <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
    
    <div class="content flex-grow-1"></div>