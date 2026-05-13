<?php
session_start();
require_once "./layout/header.php";
require_once "../db.php";
require_once "../models/Enrollment.php";

$student_id = $_SESSION['user_id'];


$enroll_obj = new Enrollment($pdo);

$my_enrollments = $enroll_obj->getStudentEnrollments($student_id);

?>

<div id="layoutSidenav_content" class="w-100">
    <main>
        <div class="container-fluid px-4">

            <div class="container-fluid px-4">
                <h1 class="mt-4">Enrollments</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">My courses</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-list me-1"></i>
                        Enrollment Records
                        <a href="./courses.php" class="btn btn-primary btn-sm float-end">All Courses</a>
                    </div>
                    <div class="card-body">
                        <table id="datatablesSimple">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_enrollments as $enrollment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['user_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($enrollment['enrolled_date'])); ?></td>
                                        <td>
                                            <?php
                                            $badge = 'bg-primary';
                                            if ($enrollment['status'] == 'completed') $badge = 'bg-success';
                                            if ($enrollment['status'] == 'cancelled') $badge = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $badge; ?>"><?php echo $enrollment['status']; ?></span>
                                        </td>
                                        <td>
                                            <a href="./edit_enrollment.php?id=<?php echo $enrollment['id']; ?>&<?php echo $enrollment['c_id']; ?>" class="btn btn-sm btn-primary">Update Status</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once "./layout/footer.php"; ?>