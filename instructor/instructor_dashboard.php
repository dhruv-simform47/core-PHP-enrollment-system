<?php
session_start();
require_once "../db.php";
require_once "./layout/header.php"; 

$instructor_id = $_SESSION['user_id'];


$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status != 'cancelled') as student_count
          FROM courses c 
          WHERE c.instructor_id = ? 
          ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$instructor_id]);
$my_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">My Assigned Courses</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-book me-1"></i> Teaching Schedule
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Duration</th>
                                <th>Total Students</th>
                                <th>Max Seats</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo $course['duration_weeks']; ?> Weeks</td>
                                <td><strong><?php echo $course['student_count']; ?></strong></td>
                                <td><?php echo $course['max_seats']; ?></td>
                                <td>
                                    <a href="./view_students.php?course_id=<?php echo $course['id']; ?>" class="btn btn-sm btn-info text-white">
                                        <i class="fas fa-users"></i> View Students
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
<?php require_once "./layout/footer.php"; ?>