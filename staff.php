<?php
session_start();
require_once('db_conn.php');
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'ADMIN') { header("Location: login.php"); exit(); }
include('includes/header.php'); 
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-white text-shadow"><i class="fas fa-users-cog me-2"></i>Staff Management</h2>
        <a href="staff_add.php" class="btn btn-warning fw-bold shadow-sm px-4 rounded-pill">
            <i class="fas fa-user-plus me-2"></i>Add New Staff
        </a>
    </div>

    <div class="glass-card p-4">
        
        <div class="row mb-4 align-items-center justify-content-between">
            <div class="col-md-4 d-flex align-items-center">
                <span class="text-muted fw-bold small me-2">Show</span>
                <select id="customLength" class="form-select form-select-sm border-0 bg-light shadow-sm text-center fw-bold" style="width: 70px; border-radius: 10px;">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                </select>
                <span class="text-muted fw-bold small ms-2">staff</span>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <div class="input-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-search text-secondary"></i></span>
                    <input type="text" id="customSearch" class="form-control border-0 bg-white" placeholder="Search staff name, ID or role...">
                    <button class="btn btn-primary px-4 fw-bold" style="background: linear-gradient(45deg, #667eea, #764ba2); border: none;">Search</button>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle w-100 no-search" id="tableStaff">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Manager</th> <th>Phone No.</th>
                    <th class="text-end">Salary (RM)</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // SQL Fix untuk elak error array key
                $sql = "SELECT s.StaffId, s.StaffName, s.StaffRole, s.StaffPhone, s.StaffSalary, 
                        m.StaffName AS MANAGER_NAME 
                        FROM STAFFS s 
                        LEFT JOIN STAFFS m ON s.ManagerId = m.StaffId 
                        ORDER BY s.StaffId ASC";
                
                $stmt = oci_parse($dbconn, $sql); 
                oci_execute($stmt);
                
                while ($row = oci_fetch_array($stmt, OCI_ASSOC)) {
                    $roleBadge = ($row['STAFFROLE'] == 'ADMIN') ? 'bg-danger' : 'bg-primary';
                    $manager = isset($row['MANAGER_NAME']) ? $row['MANAGER_NAME'] : null;

                    echo "<tr>";
                    echo "<td class='text-muted small'>" . $row['STAFFID'] . "</td>";
                    echo "<td>
                            <div class='d-flex align-items-center'>
                                <div class='bg-light rounded-circle p-2 me-3 shadow-sm d-flex justify-content-center align-items-center' style='width:40px;height:40px;'>
                                    <i class='fas fa-user-tie text-primary'></i>
                                </div>
                                <strong>" . htmlspecialchars($row['STAFFNAME']) . "</strong>
                            </div>
                          </td>";
                    echo "<td><span class='badge $roleBadge rounded-pill px-3 shadow-sm'>" . $row['STAFFROLE'] . "</span></td>";
                    
                    // Column Manager
                    echo "<td>" . ($manager ? '<small class="text-muted"><i class="fas fa-user-shield me-1"></i>'.$manager.'</small>' : '<span class="text-muted">-</span>') . "</td>";
                    
                    echo "<td>" . $row['STAFFPHONE'] . "</td>";
                    echo "<td class='text-end fw-bold'>RM " . number_format($row['STAFFSALARY'], 2) . "</td>";
                    echo "<td class='text-center'>
                            <a href='staff_edit.php?id=".$row['STAFFID']."' class='btn btn-sm btn-light text-primary shadow-sm rounded-circle' style='width:35px;height:35px;'><i class='fas fa-pen'></i></a>
                            <button onclick='confirmDelete(\"".$row['STAFFID']."\")' class='btn btn-sm btn-light text-danger shadow-sm rounded-circle ms-1' style='width:35px;height:35px;'><i class='fas fa-trash'></i></button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#tableStaff').DataTable({
            "dom": "rtip",
            "pageLength": 10,
            "columnDefs": [{ "orderable": false, "targets": [6] }],
            "language": {
                "info": "<span class='text-muted small'>Showing _START_ to _END_ of _TOTAL_ staff</span>",
                "paginate": { "next": "<i class='fas fa-chevron-right small'></i>", "previous": "<i class='fas fa-chevron-left small'></i>" }
            }
        });
        $('#customSearch').on('keyup', function() { table.search(this.value).draw(); });
        $('#customLength').on('change', function() { table.page.len(this.value).draw(); });
    });

    function confirmDelete(id) {
        Swal.fire({
            title: 'Delete Staff?', text: "Are you sure?", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, Delete!'
        }).then((result) => { if (result.isConfirmed) { window.location.href = 'staff_delete.php?id=' + id; } })
    }
</script>
</body>
</html>