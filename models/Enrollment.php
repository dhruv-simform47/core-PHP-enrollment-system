<?php
class Enrollment
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getAllEnrollments()
    {
        $query = "SELECT e.*, s.user_name AS student, s.uuid as student_id, c.id as course_id, c.course_name AS course 
                  FROM enrollments e 
                  JOIN users s ON e.student_id = s.uuid 
                  JOIN courses c ON e.course_id = c.id 
                  ORDER BY e.enrolled_date DESC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentEnrollments($student_id)
    {
        $query = "SELECT e.*, c.course_name, c.id as c_id, i.user_name 
                  FROM enrollments e 
                  JOIN courses c ON e.course_id = c.id 
                  JOIN users i ON c.instructor_id = i.uuid 
                  WHERE e.student_id = ? 
                  ORDER BY e.enrolled_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAvailableCoursesForStudent($student_id)
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

    public function getById($enroll_id)
    {
        $stmt = $this->db->prepare("SELECT e.*, s.user_name, c.course_name, c.id as course_id
                                    FROM enrollments e 
                                    JOIN users s ON e.student_id = s.uuid 
                                    JOIN courses c ON e.course_id = c.id 
                                    WHERE e.id = ?");
        $stmt->execute([$enroll_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStudentsByCourse($course_id, $instructor_id)
    {
        $query = "SELECT e.id as enrollment_id, e.status, e.enrolled_date, u.user_name, u.email 
              FROM enrollments e 
              JOIN users u ON e.student_id = u.uuid 
              JOIN courses c ON e.course_id = c.id
              WHERE e.course_id = ? AND c.instructor_id = ? 
              ORDER BY u.user_name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$course_id, $instructor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnrollmentForInstructor($enroll_id, $instructor_id)
    {
        $query = "SELECT e.*, u.user_name, c.course_name, c.id as course_id 
              FROM enrollments e
              JOIN users u ON e.student_id = u.uuid
              JOIN courses c ON e.course_id = c.id
              WHERE e.id = ? AND c.instructor_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$enroll_id, $instructor_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function enroll($student_id, $course_id)
    {
        try {
            $this->db->beginTransaction();

            if ($this->isAlreadyEnrolled($student_id, $course_id)) {
                $this->db->rollBack();
                return ['status' => 'error', 'message' => 'Already enrolled.'];
            }

            if (!$this->hasAvailableSeats($course_id, true)) {
                $this->db->rollBack();
                return ['status' => 'error', 'message' => 'Course is full.'];
            }

            require_once "../uuid_generator.php";
            $enrollId = generateUUIDv4();
            $stmt = $this->db->prepare("INSERT INTO enrollments (id, student_id, course_id, status) VALUES (?, ?, ?, 'active')");
            $stmt->execute([$enrollId, $student_id, $course_id]);

            $this->db->commit();
            return ['status' => 'success', 'message' => 'Successfully enrolled!'];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Enrollment::enroll Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return ['status' => 'error', 'message' => 'Database error during enrollment.'];
        }
    }

    public function updateStatus($enroll_id, $status, $student_id = null)
    {
        try {
            $this->db->beginTransaction();

            if ($status === 'active') {
                $courseStmt = $this->db->prepare("SELECT course_id FROM enrollments WHERE id = ?");
                $courseStmt->execute([$enroll_id]);
                $course_id = $courseStmt->fetchColumn();

                if ($course_id && !$this->hasAvailableSeats($course_id, true)) {
                    $this->db->rollBack();
                    return ['status' => 'error', 'message' => 'Cannot activate: Course is full.'];
                }
            }

            $query = "UPDATE enrollments SET status = ? WHERE id = ?";
            $params = [$status, $enroll_id];

            if ($student_id) {
                $query .= " AND student_id = ?";
                $params[] = $student_id;
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->db->commit();
                return ['status' => 'success', 'message' => 'Status updated.'];
            } else {
                $this->db->rollBack();
                return ['status' => 'error', 'message' => 'No changes made or unauthorized.'];
            }

        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Enrollment::updateStatus Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return ['status' => 'error', 'message' => 'Database error updating status.'];
        }
    }

    private function isAlreadyEnrolled($student_id, $course_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? AND status != 'cancelled'");
        $stmt->execute([$student_id, $course_id]);
        return (bool)$stmt->fetch();
    }

    private function hasAvailableSeats($course_id, $lock = false)
    {
        $forUpdate = $lock ? " FOR UPDATE" : "";
        $stmt = $this->db->prepare("SELECT max_seats, 
                  (SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND status != 'cancelled') as current_enrolls 
                  FROM courses WHERE id = ?" . $forUpdate);
        $stmt->execute([$course_id, $course_id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data['current_enrolls'] < $data['max_seats'];
    }
}