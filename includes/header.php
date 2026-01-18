<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FruitHub Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* CSS STYLE SAMA MACAM TADI */
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 0;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .sidebar {
            height: 100vh; width: 260px;
            background: rgba(33, 37, 41, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            position: fixed; top: 0; left: 0; z-index: 1000;
            display: flex; flex-direction: column;
            overflow-y: auto; scrollbar-width: none; -ms-overflow-style: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }
        .sidebar-header {
            padding: 25px 20px; background: rgba(0, 0, 0, 0.2);
            text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); flex-shrink: 0;
        }
        .sidebar a {
            color: #cfd2d6; text-decoration: none; display: block;
            padding: 12px 20px; margin: 4px 10px; border-radius: 8px;
            transition: all 0.2s ease-in-out; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.15); color: #fff; transform: translateX(5px);
        }
        .sidebar .section-title {
            font-size: 0.7rem; text-transform: uppercase; color: #6c757d;
            padding: 20px 20px 5px; font-weight: bold; letter-spacing: 1px;
        }
        .content-wrapper { margin-left: 260px; padding: 30px; width: calc(100% - 260px); transition: all 0.3s; }
        .glass-card {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);
            border-radius: 15px; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18); margin-bottom: 20px; overflow: hidden;
        }
        .btn-pos {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            color: #000; font-weight: bold; border: none;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        }
        .btn-pos:hover { transform: scale(1.02); background: #fff; color: #000; }
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter { margin-bottom: 15px; }
        .no-search-wrapper .dataTables_filter, .no-search-wrapper .dataTables_length { display: none !important; }
        @media print {
            .sidebar, .btn, .d-print-none, .dataTables_wrapper .dataTables_filter { display: none !important; }
            .content-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            body { background: white !important; }
            .glass-card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold mb-0 text-white"><i class="fas fa-apple-alt me-2 text-success"></i>FruitHub</h4>
            <small class="text-white-50">System v2.0</small>
        </div>
        
        <div class="flex-grow-1 overflow-auto py-3">
            <div class="px-3 mb-3">
                <a href="sales_form.php" class="btn btn-pos w-100 text-start text-dark"><i class="fas fa-cash-register me-2"></i> New Sales</a>
            </div>
            
            <?php if ($role == 'ADMIN') { ?>
                <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home me-2"></i> Dashboard</a>
            <?php } else { ?>
                <a href="staff_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home me-2"></i> Dashboard</a>
            <?php } ?>
            
            <div class="section-title">Operations</div>
            <a href="fruits.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'fruits.php' ? 'active' : ''; ?>"><i class="fas fa-boxes me-2"></i> Fruit Stock</a>
            <a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>"><i class="fas fa-receipt me-2"></i> Sales History</a>
            <a href="customer.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'customer.php' ? 'active' : ''; ?>"><i class="fas fa-users me-2"></i> Customers</a>
            
            <a href="report_list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'report_list.php' ? 'active' : ''; ?>"><i class="fas fa-chart-pie me-2"></i> Report List</a>

            <?php if ($role == 'ADMIN') { ?>
                <div class="section-title">Administration</div>
                <a href="staff.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff.php' ? 'active' : ''; ?>"><i class="fas fa-users-cog me-2"></i> Staff Management</a>
                <a href="supplier.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'supplier.php' ? 'active' : ''; ?>"><i class="fas fa-truck me-2"></i> Suppliers</a>
            <?php } ?>
        </div>

        <div class="p-3 border-top border-secondary mt-auto">
            <a href="profile.php"><i class="fas fa-user-circle me-2"></i> My Profile</a>
            <a href="logout.php" class="text-danger mt-1"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>
    
    <div class="content-wrapper">

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.table:not(.no-search)').DataTable({
            "language": { "search": "", "searchPlaceholder": "Search...", "lengthMenu": "_MENU_" },
            "pageLength": 10, "lengthChange": true
        });
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>