<?php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
$loggedInName = $_SESSION['user_name'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Dashboard - SB Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="./admin_dashboard.php">Admin Site </a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4  me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i> </button>
        <!-- Navbar Search-->
        <a class="btn btn-secondary btn-sm ms-auto me-4" id="logout" href="../auth/logout.php">Logout</a>

    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="./admin_dashboard.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <div class="sb-sidenav-menu-heading">Interface</div>

                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTables" aria-expanded="false" aria-controls="collapsePages">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            Tables
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseTables" aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                                <!-- student -->
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#tableCollapseStudents" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                    Students
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse" id="tableCollapseStudents" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="./add_student.php">Add New</a>
                                        <a class="nav-link" href="./students.php">View All</a>
                                        <a class="nav-link" href="./addStudent_csv.php">add using csv</a>
                                    </nav>
                                </div>
                                <!-- instructor -->
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#tableCollapseInstructors" aria-expanded="false" aria-controls="pagesCollapseError">
                                    Instructors
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>

                                <div class="collapse" id="tableCollapseInstructors" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="./add_instructor.php">Add New </a>
                                        <a class="nav-link" href="./instructors.php">View All</a>
                                    </nav>
                                </div>

                                <!-- admin -->
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#tableCollapseAdmins" aria-expanded="false" aria-controls="pagesCollapseError">
                                    Admins
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>


                                <div class="collapse" id="tableCollapseAdmins" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="./add_admin.php">Add New </a>
                                        <a class="nav-link" href="./admins.php">View All</a>
                                    </nav>
                                </div>
                                <!-- Courses -->
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#tableCollapseCourses" aria-expanded="false" aria-controls="pagesCollapseError">
                                    Courses
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>


                                <div class="collapse" id="tableCollapseCourses" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="./add_course.php">Add New </a>
                                        <a class="nav-link" href="./courses.php">View All</a>
                                    </nav>
                                </div>
                                <!-- enrollent -->
                                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#tableCollapseEnroll" aria-expanded="false" aria-controls="pagesCollapseError">
                                    Enrollments
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                                </a>


                                <div class="collapse" id="tableCollapseEnroll" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordionPages">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="./add_enrollment.php"> New Enroll</a>
                                        <a class="nav-link" href="./enrollments.php">View All</a>
                                    </nav>
                                </div>
                            </nav>
                        </div>

                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?php echo htmlspecialchars($loggedInName); ?>
                </div>
            </nav>
        </div>