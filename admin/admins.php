<?php
session_start();
require_once "../db.php";
require_once "../models/Admin.php";

$admin_obj=new Admin($pdo);
$admins =$admin_obj->getAll();

require_once "./includes/header.php";
?>
<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">admins</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">admins</li>
            </ol>
            <div class="card mb-4">
                <div class="card-header">                   
                    <i class="fas fa-user-graduates me-1"></i>
                    admins List
                    <a href="./add_admin.php" class="btn btn-primary btn-sm float-end">Add New admin</a>
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
                            <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td>
                                    <?php echo $admin['is_verified'] ? 
                                        '<span class="badge bg-success">Verified</span>' : 
                                        '<span class="badge bg-warning">Pending</span>'; ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($admin['created_at'])); ?></td>
                                <td>
                                    <a href="./edit_admin.php?id=<?php echo $admin['uuid']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="./delete_admin.php?id=<?php echo $admin['uuid']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin?')">Delete</a>
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