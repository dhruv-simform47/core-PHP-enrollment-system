<?php
session_start();
require_once "../db.php";
require_once "../models/Student.php";

$students = (new Student($pdo))->getAll();

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Students</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Students</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">                   
                    <!-- <i class="fas fa-user-graduates me-1"></i> -->
                     <i class="fas fa-list me-1"></i>
                    Students List
                    <a href="./add_student.php" class="btn btn-primary btn-sm float-end">Add New Student</a>
                </div>
                <div class="card-body">
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>    
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td>
                                    <?php echo $student['is_verified'] ? 
                                        '<span class="badge bg-success">Verified</span>' : 
                                        '<span class="badge bg-warning">Pending</span>'; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <a href="./edit_student.php?id=<?php echo $student['uuid']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="./delete_student.php?id=<?php echo $student['uuid']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?')">Delete</a>
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