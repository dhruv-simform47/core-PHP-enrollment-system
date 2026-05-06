<?php

class Enrollment
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }
    //for specific student get all enrolled courses 
    public function getAllStudentCourses($student_id)
    {

        $query = "SELECT e.*, c.course_name, i.user_name 
          FROM enrollments e 
          JOIN courses c ON e.course_id = c.id 
          JOIN users i ON c.instructor_id = i.uuid 
          WHERE e.student_id = ? 
          ORDER BY e.enrolled_date DESC";

        $stmt = $this->db->prepare($query);


        $stmt->execute([$student_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get all courses with enrollment status for a specific student
    public function getAllCoursesWithStatus($student_id)
    {
        $query = "SELECT c.*, u.user_name as instructor_name,
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status != 'cancelled') as current_enrolls,
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND student_id = ? AND status != 'cancelled') as is_enrolled
                  FROM courses c
                  JOIN users u ON c.instructor_id = u.uuid
                  ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Handle the enrollment logic (Replaces logic in enroll_process.php)
    public function enroll($student_id, $course_id)
    {
        // Check if already enrolled
        if ($this->isAlreadyEnrolled($student_id, $course_id)) {
            return ['status' => 'error', 'message' => 'Already enrolled.'];
        }

        // Check seats
        if (!$this->hasAvailableSeats($course_id)) {
            return ['status' => 'error', 'message' => 'Course is full.'];
        }

        $enrollId = generateUUIDv4(); // Assuming global or passed in
        $stmt = $this->db->prepare("INSERT INTO enrollments (id, student_id, course_id, status) VALUES (?, ?, ?, 'active')");

        if ($stmt->execute([$enrollId, $student_id, $course_id])) {
            return ['status' => 'success', 'message' => 'Successfully enrolled!'];
        }
        return ['status' => 'error', 'message' => 'Database error.'];
    }
    //for specific course fetch details used in edit form
    public function getCourseDetail($enrollment_id, $student_id)
    {
        $stmt = $this->db->prepare("SELECT e.*, c.course_name, i.user_name as instructor_name 
                       FROM enrollments e 
                       JOIN courses c ON e.course_id = c.id 
                       JOIN users i ON c.instructor_id = i.uuid 
                       WHERE e.id = ? AND e.student_id = ?");
        $stmt->execute([$enrollment_id, $student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateCourseStatus($enrollment_id, $student_id, $new_status)
    {
        $query = "UPDATE enrollments SET status = ? WHERE id = ? AND student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$new_status, $enrollment_id, $student_id]);
        return ['status' => 'success', 'message' => 'Enrollment status updated!'];
    }
    // following are helper methods to check  before enrolling student

    private function isAlreadyEnrolled($student_id, $course_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? AND status != 'cancelled'");
        $stmt->execute([$student_id, $course_id]);
        return (bool)$stmt->fetch();
    }

    private function hasAvailableSeats($course_id)
    {
        $stmt = $this->db->prepare("SELECT max_seats, 
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status != 'cancelled') as current_enrolls 
                  FROM courses WHERE id = ?");
        $stmt->execute([$course_id, $course_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['current_enrolls'] < $data['max_seats'];
    }
}
