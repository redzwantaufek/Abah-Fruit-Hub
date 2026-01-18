<?php
session_start();
require_once('db_conn.php');

// Security Check: Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

include('includes/header.php'); 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-white fw-bold text-shadow">
            <i class="fas fa-chart-bar me-2"></i>Report & Analytics Hub
        </h2>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <div class="mb-3 text-primary">
                    <i class="fas fa-file-invoice-dollar fa-4x"></i>
                </div>
                <h4 class="fw-bold">Monthly Sales</h4>
                <p class="text-muted small">Detailed view of sales revenue, units sold, and grand totals filtered by month and year.</p>
                <a href="monthly_report.php" class="btn btn-primary w-100 fw-bold">Open Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <div class="mb-3 text-danger">
                    <i class="fas fa-exclamation-triangle fa-4x"></i>
                </div>
                <h4 class="fw-bold">Inventory Alerts</h4>
                <p class="text-muted small">Track low stock items (< 15 units) and fruits that are expiring within the next 7 days.</p>
                <a href="inventory_report.php" class="btn btn-danger w-100 fw-bold">Open Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <div class="mb-3 text-success">
                    <i class="fas fa-medal fa-4x"></i>
                </div>
                <h4 class="fw-bold">Top Products</h4>
                <p class="text-muted small">Visual analysis of the top 5 best-selling fruits to help with future stock planning.</p>
                <a href="topselling_fruit_report.php" class="btn btn-success w-100 fw-bold">Open Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <div class="mb-3 text-warning">
                    <i class="fas fa-id-badge fa-4x"></i>
                </div>
                <h4 class="fw-bold">Staff Performance</h4>
                <p class="text-muted small">Analyze transactions and total sales collection for each employee in the system.</p>
                <a href="staff_performance.php" class="btn btn-warning w-100 fw-bold">Open Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="glass-card h-100 text-center p-4">
                <div class="mb-3 text-info">
                    <i class="fas fa-users-cog fa-4x"></i>
                </div>
                <h4 class="fw-bold">Customer Lists</h4>
                <p class="text-muted small">Quick exportable list of all registered customers and their contact details.</p>
                <a href="customer.php" class="btn btn-info text-white w-100 fw-bold">Open List</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>