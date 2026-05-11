<?php
class Course
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getFullDetails()
    {
        $query = "SELECT c.*, u.user_name AS instructor_name, 
          (c.max_seats - (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id)) AS vacant_seats
          FROM courses c
          JOIN users u ON c.instructor_id = u.uuid";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM courses ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount()
    {
        $query = "SELECT COUNT(*) as course_count FROM courses";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }

    public function add($course_id, $course_name, $description, $instructor_id, $duration, $max_seats)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO courses (id, course_name, description, instructor_id, duration_weeks, max_seats) 
                VALUES (:id, :course_name, :description, :instructor_id, :duration, :max_seats)
            ");
            $stmt->execute([
                ':id' => $course_id,
                ':course_name' => $course_name,
                ':description' => $description,
                ':instructor_id' => $instructor_id,
                ':duration' => $duration,
                ':max_seats' => $max_seats
            ]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Course::add Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Course::delete Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function edit($course_name, $description, $instructor_id, $duration, $max_seats, $id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE courses SET course_name = ?, description = ?, instructor_id = ?, duration_weeks = ?, max_seats = ? WHERE id = ?");
            $stmt->execute([$course_name, $description, $instructor_id, $duration, $max_seats, $id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Course::edit Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function getByInstructor($instructor_id)
    {
        $query = "SELECT c.*, 
              (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND status != 'cancelled') as student_count
              FROM courses c 
              WHERE c.instructor_id = ? 
              ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$instructor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByIdAndInstructor($course_id, $instructor_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ? AND instructor_id = ?");
        $stmt->execute([$course_id, $instructor_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}