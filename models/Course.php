<?php
//this class is used by admin only
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
    public  function getAll()
    {
        //get all course from User Table
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
        //add student logic 

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
        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        //remove student from db
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function edit($course_name, $description, $instructor_id, $duration, $max_seats, $id)
    {
        //edit student details 

        $stmt = $this->db->prepare("UPDATE courses SET course_name = ?, description = ?, instructor_id = ?, duration_weeks = ?, max_seats = ? WHERE id = ?");
        $stmt->execute([$course_name, $description, $instructor_id, $duration, $max_seats, $id]);
        return $stmt->rowCount() > 0;
    }

    //instructor related method 
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
