<?php
session_start();
require_once "../db.php";
$query = "SELECT e.*, s.user_name AS student, c.course_name AS course 
          FROM enrollments e 
          JOIN users s ON e.student_id = s.uuid 
          JOIN courses c ON e.course_id = c.id 
          ORDER BY e.enrolled_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Enrollments</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Enrollments</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">                   
                    <i class="fas fa-list me-1"></i>
                    Enrollment Records
                    <a href="./add_enrollment.php" class="btn btn-primary btn-sm float-end">Add New Enrollment</a>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>    
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($enrollment['student']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['course']); ?></td>
                                <td><?php echo date('d M Y', strtotime($enrollment['enrolled_date'])); ?></td>
                                <td>
                                    <?php 
                                    $badge = 'bg-primary';
                                    if($enrollment['status'] == 'completed') $badge = 'bg-success';
                                    if($enrollment['status'] == 'cancelled') $badge = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo $enrollment['status']; ?></span>
                                </td>
                                <td>
                                    <a href="./edit_enrollment.php?id=<?php echo $enrollment['id']; ?>" class="btn btn-sm btn-primary">Update Status</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
<?php require_once "./includes/footer.php"; ?>