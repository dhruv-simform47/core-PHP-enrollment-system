
<?php
session_start();
require_once "./includes/header.php"; 
require_once "../db.php";


$stu_sql="SELECT COUNT(*) as stu_count FROM users WHERE role='student'";
$admin_sql="SELECT COUNT(*) as admin_count FROM users WHERE role='admin'";
$instructor_sql="SELECT COUNT(*) as instructor_count FROM users WHERE role='instructor'";
$course_sql="SELECT COUNT(*) as course_count FROM courses";


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
                        <div class="card-body">Students<p id="stu_count" class="small text-white "><h5 class="border rounded d-inline p-2"><?php  $stmt=$pdo->query($stu_sql); echo $stmt->fetchColumn(); ?></h5> </p></div>
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
                        <div class="card-body">Instructors<p id="stu_count" class="small text-white"><h5 class="border rounded d-inline p-2"><?php  $stmt=$pdo->query($instructor_sql); echo $stmt->fetchColumn(); ?></h5></p></div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white" href="./instructors.php">View Details</a>
                            <a class="small text-white" href="./add_instructor.php">Add New</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">Courses <p id="stu_count" class="small text-white"><h5 class="border rounded d-inline p-2"><?php  $stmt=$pdo->query($course_sql); echo $stmt->fetchColumn(); ?></h5> </p></div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white" href="./courses.php">View Details</a>
                            <a class="small text-white" href="./add_course.php">Add New</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-body">Admins <p id="stu_count" class="small text-white"><h5 class="border rounded d-inline p-2"><?php  $stmt=$pdo->query($admin_sql); echo $stmt->fetchColumn(); ?></h5></p> </div>
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