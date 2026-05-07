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
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">Students<p id="stu_count" class="small text-white ">
                            <h5 class="border rounded d-inline p-2">
                                <?php $stu_obj = new Student($pdo);
                                echo $stu_obj->getCount(); ?></h5>
                            </p>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white" href="./students.php">View Details</a>
                            <a class="small text-white" href="./add_student.php">Add New</a>
                            <a class="small text-white" href="./addStudent_csv.php">Add using csv</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">Instructors<p id="stu_count" class="small text-white">
                            <h5 class="border rounded d-inline p-2">
                                <?php $instructor_obj = new Instructor($pdo);
                                echo $instructor_obj->getCount(); ?></h5>
                            </p>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white" href="./instructors.php">View Details</a>
                            <a class="small text-white" href="./add_instructor.php">Add New</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">Courses <p id="stu_count" class="small text-white">
                            <h5 class="border rounded d-inline p-2">
                                <?php $course_obj = new Course($pdo);
                                echo $course_obj->getCount(); ?>
                            </h5>
                            </p>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white" href="./courses.php">View Details</a>
                            <a class="small text-white" href="./add_course.php">Add New</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-body">Admins <p id="stu_count" class="small text-white">
                            <h5 class="border rounded d-inline p-2">
                                <?php $admin_obj = new Admin($pdo);
                                echo $admin_obj->getCount(); ?></h5>
                            </p>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white" href="./admins.php">View Details</a>
                            <a class="small text-white" href="./add_admin.php">Add New</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once "./includes/footer.php"; ?>