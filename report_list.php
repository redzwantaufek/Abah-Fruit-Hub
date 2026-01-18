<?php
session_start();
require_once('db_conn.php');
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include('includes/header.php'); 
?>

<div class="container-fluid">
    <h2 class="fw-bold text-white text-shadow mb-4"><i class="fas fa-chart-pie me-2"></i>Analytics & Report Hub</h2>

    <div class="row g-4">
        
        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-5 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="fas fa-file-invoice-dollar fa-5x text-primary"></i>
                </div>
                <div class="mb-3 text-primary">
                    <i class="fas fa-calendar-alt fa-4x"></i> 
                </div>
                <h4 class="fw-bold mt-3">Monthly Sales</h4>
                <p class="text-muted small">View detailed sales revenue broken down by month and year.</p>
                <a href="monthly_report.php" class="btn btn-primary w-100 fw-bold shadow mt-2 rounded-pill">View Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-5 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="fas fa-exclamation-triangle fa-5x text-danger"></i>
                </div>
                <div class="mb-3 text-danger">
                    <i class="fas fa-boxes fa-4x"></i> 
                </div>
                <h4 class="fw-bold mt-3">Inventory Alerts</h4>
                <p class="text-muted small">Track low stock items and expiring products urgently.</p>
                <a href="inventory_report.php" class="btn btn-danger w-100 fw-bold shadow mt-2 rounded-pill">View Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-5 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-3 opacity-25">
                    <i class="fas fa-chart-line fa-5x text-warning"></i>
                </div>
                <div class="mb-3 text-warning">
                    <i class="fas fa-user-tie fa-4x"></i> 
                </div>
                <h4 class="fw-bold mt-3">Staff Performance</h4>
                <p class="text-muted small">Analyze sales collection and total transactions by employee.</p>
                <a href="staff_performance.php" class="btn btn-warning w-100 fw-bold shadow mt-2 rounded-pill">View Report</a>
            </div>
        </div>

    </div>
</div>
</body>
</html>