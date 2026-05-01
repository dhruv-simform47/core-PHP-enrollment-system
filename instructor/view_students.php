<?php
session_start();
require_once "../db.php";
require_once "./layout/header.php";

$instructor_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? '';

// SECURITY: Ensure this instructor actually teaches this course before showing students
$securityStmt = $pdo->prepare("SELECT course_name FROM courses WHERE id = ? AND instructor_id = ?");
$securityStmt->execute([$course_id, $instructor_id]);
$course = $securityStmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<div class='container-fluid px-4'><div class='alert alert-danger mt-4'>Access Denied or Course Not Found.</div></div>";
    exit();
}

// Fetch students enrolled in this course
$query = "SELECT e.id as enrollment_id, e.status, e.enrolled_date, u.user_name, u.email 
          FROM enrollments e 
          JOIN users u ON e.student_id = u.uuid 
          WHERE e.course_id = ? 
          ORDER BY u.user_name ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([$course_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Students: <?php echo htmlspecialchars($course['course_name']); ?></h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="instructor_dashboard.php">My Courses</a></li>
                <li class="breadcrumb-item active">Student List</li>
            </ol>
            
            <div class="card mb-4">
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['enrolled_date'])); ?></td>
                                <td>
                                    <?php 
                                        $badge = ($row['status'] == 'completed') ? 'bg-success' : (($row['status'] == 'cancelled') ? 'bg-danger' : 'bg-primary');
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($row['status']); ?></span>
                                </td>
                                <td>
                                   
                                    <a href="manage_student.php?enroll_id=<?php echo $row['enrollment_id']; ?>" class="btn btn-sm btn-primary">Manage</a>
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