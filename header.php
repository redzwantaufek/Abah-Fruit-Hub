<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fruit Stall Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #cfd2d6; text-decoration: none; display: block; padding: 10px; }
        .sidebar a:hover { background-color: #495057; color: white; }
        .content { padding: 20px; }
        .card-custom { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="d-flex">
    <div class="sidebar p-3" style="width: 250px;">
        <h4 class="text-center mb-4">🍏 FruitHub</h4>
        <a href="admin_dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
        <a href="fruits.php"><i class="fas fa-apple-alt me-2"></i> Urus Buah</a>
        <a href="staff.php"><i class="fas fa-users me-2"></i> Urus Staff</a>
        <a href="customer.php"><i class="fas fa-user-friends me-2"></i> Urus Pelanggan</a>
        <a href="supplier.php"><i class="fas fa-truck me-2"></i> Urus Supplier</a>
        <a href="orders.php"><i class="fas fa-receipt me-2"></i> Rekod Jualan</a>
        <hr>
        <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
    
    <div class="content flex-grow-1"></div>