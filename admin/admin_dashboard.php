<?php
session_start();
require_once "./includes/header.php";
require_once "../db.php";
require_once "../models/Admin.php";
require_once "../models/Student.php";
require_once "../models/Course.php";
require_once "../models/Instructor.php";
?>

<div id="layoutSidenav_content" class="w-100">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Dashboard</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Dashboard Overview</li>
            </ol>
            <div class="row">
                
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>Total Students</div>
                            <h3 class="mt-2">
                                <?php 
                                    $stu_obj = new Student($pdo);
                                    echo $stu_obj->getCount(); 
                                ?>
                            </h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./students.php">Manage Students</a>
                            <a class="small text-white stretched-link text-decoration-none" href="./addStudent_csv.php">Upload via CSV</a>
                        </div>
                    </div>
                   
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>Total Instructors</div>
                            <h3 class="mt-2">
                                <?php 
                                    $instructor_obj = new Instructor($pdo);
                                    echo $instructor_obj->getCount(); 
                                ?>
                            </h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./instructors.php">Manage Instructors</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>Active Courses</div>
                            <h3 class="mt-2">
                                <?php 
                                    $course_obj = new Course($pdo);
                                    echo $course_obj->getCount(); 
                                ?>
                            </h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./courses.php">Manage Courses</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-dark text-white mb-4 shadow-sm">
                        <div class="card-body">
                            <div>System Admins</div>
                            <h3 class="mt-2">
                                <?php 
                                    $admin_obj = new Admin($pdo);
                                    echo $admin_obj->getCount(); 
                                ?>
                            </h3>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link text-decoration-none" href="./admins.php">Manage Admins</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php require_once "./includes/footer.php"; ?>