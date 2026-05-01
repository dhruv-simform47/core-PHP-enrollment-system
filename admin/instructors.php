<?php
session_start();
require_once "../db.php";

$stmt = $pdo->prepare("SELECT uuid, user_name, email, is_verified, created_at FROM users WHERE role = 'instructor' ORDER BY created_at DESC");
$stmt->execute();
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Instructors</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Instructors</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">                   
                    <!-- <i class="fas fa-user-graduates me-1"></i> -->
                    <i class="fas fa-list me-1"></i>
                    Instructors List
                    <a href="./add_instructor.php" class="btn btn-primary btn-sm float-end">Add New Instructor</a>
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
                            <?php foreach ($instructors as $instructor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($instructor['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                                <td>
                                    <?php echo $instructor['is_verified'] ? 
                                        '<span class="badge bg-success">Verified</span>' : 
                                        '<span class="badge bg-warning">Pending</span>'; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($instructor['created_at'])); ?></td>
                                <td>
                                    <a href="./edit_instructor.php?id=<?php echo $instructor['uuid']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="./delete_instructor.php?id=<?php echo $instructor['uuid']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this instructor?')">Delete</a>
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