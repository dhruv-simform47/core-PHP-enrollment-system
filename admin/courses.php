<?php
session_start();
require_once "../db.php";
require_once "../models/Course.php";


$course_obj=new Course($pdo);
$courses = $course_obj->getFullDetails();



require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Courses</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Courses</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">                   
                    <i class="fas fa-book me-1"></i>
                    Courses List
                    <a href="./add_course.php" class="btn btn-primary btn-sm float-end">Add New Course</a>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Instructor</th>
                                <th>Duration(weeks)</th>
                                <th>Vacant Seats</th>
                                <th>Max Seats</th>
                                <th>Actions</th>
                            </tr>    
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($course['description'], 0, 50)) . '...'; ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['duration_weeks']); ?></td>
                                <td><?php echo htmlspecialchars($course['vacant_seats']); ?></td>
                                <td><?php echo htmlspecialchars($course['max_seats']); ?></td>
                                <td>
                                    <a href="./edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="./delete_course.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
<?php 
require_once "./includes/footer.php";
?>