<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once "./includes/header.php"; 
?>

<div id="layoutSidenav_content" class="w-100">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Dashboard</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Dashboard Overview</li>
            </ol>
            
            <div class="row">
                <!-- Students Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>Total Students</div>
                            <h3 class="mt-2 stat-value" id="count-students">--</h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./students.php">Manage Students</a>
                        </div>
                    </div>
                </div>

                <!-- Instructors Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>Total Instructors</div>
                            <h3 class="mt-2 stat-value" id="count-instructors">--</h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./instructors.php">Manage Instructors</a>
                        </div>
                    </div>
                </div>

                <!-- Courses Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>Active Courses</div>
                            <h3 class="mt-2 stat-value" id="count-courses">--</h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./courses.php">Manage Courses</a>
                        </div>
                    </div>
                </div>

                <!-- Admins Card -->
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-dark text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>System Admins</div>
                            <h3 class="mt-2 stat-value" id="count-admins">--</h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./admins.php">Manage Admins</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- jQuery and AJAX Script -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function() {
        function fetchDashboardData() {
            $.ajax({
                url: '../admin_api/get_count.php',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        // Update the DOM with the data from the API
                        $('#count-students').text(res.data.students);
                        $('#count-instructors').text(res.data.instructors);
                        $('#count-courses').text(res.data.courses);
                        $('#count-admins').text(res.data.admins);
                    }
                },
                error: function() {
                    $('.stat-value').text('!'); // Show error indicator
                    console.error("Critical error fetching stats.");
                }
            });
        }

        // Initial Load
        fetchDashboardData();

        // Auto-refresh data every 5 minutes
        setInterval(fetchDashboardData, 300000);
    });
    </script>

    <?php require_once "./includes/footer.php"; ?>
</div>