<?php
session_start();
require_once "../db.php";
require_once "./layout/header.php";

$student_id = $_SESSION['user_id'];

// Query to get all courses and count current enrollments
$query = "SELECT c.*, u.user_name as instructor_name,
          (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status != 'cancelled') as current_enrolls,
          (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND student_id = ? AND status != 'cancelled') as is_enrolled
          FROM courses c
          JOIN users u ON c.instructor_id = u.uuid
          ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Available Courses</h1>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-book-open me-1"></i> Browse All Courses
                </div>
                <div class="card-body">
                    <div id="enrollAlert" class="alert" style="display:none;"></div>
                    <table id="datatablesSimple">
                        <thead>
                            <tr>
                                <th>Course Name</th>
                                <th>Instructor</th>
                                <th>Duration</th>
                                <th>Seats Available</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): 
                                $seats_left = $course['max_seats'] - $course['current_enrolls'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($course['instructor_name']); ?></td>
                                <td><?php echo $course['duration_weeks']; ?> Weeks</td>
                                <td>
                                    <?php echo $seats_left; ?> / <?php echo $course['max_seats']; ?>
                                </td>
                                <td>
                                    <?php if ($course['is_enrolled'] > 0): ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Already Enrolled</button>
                                    <?php elseif ($seats_left <= 0): ?>
                                        <button class="btn btn-danger btn-sm" disabled>Course Full</button>
                                    <?php else: ?>
                                        <button class="btn btn-success btn-sm enroll-btn" 
                                                data-id="<?php echo $course['id']; ?>">
                                            Enroll Now
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.enroll-btn').on('click', function() {
        const courseId = $(this).data('id');
        const btn = $(this);
        
        if(!confirm('Are you sure you want to enroll in this course?')) return;

        $.ajax({
            url: 'enroll_process.php',
            type: 'POST',
            data: { course_id: courseId },
            dataType: 'json',
            success: function(response) {
                const alert = $('#enrollAlert');
                if(response.status === 'success') {
                    alert.removeClass('alert-danger').addClass('alert-success').text(response.message).fadeIn();
                    btn.replaceWith('<button class="btn btn-secondary btn-sm" disabled>Already Enrolled</button>');
                } else {
                    alert.removeClass('alert-success').addClass('alert-danger').text(response.message).fadeIn();
                }
                setTimeout(() => alert.fadeOut(), 3000);
            }
        });
    });
});
</script>

<?php require_once "./layout/footer.php"; ?>