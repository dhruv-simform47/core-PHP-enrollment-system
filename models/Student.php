<?php
//this class is used by admin only
class Student
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public  function getAll()
    {
        //get all students from User Table
        return  $this->db->query("SELECT * FROM users WHERE role = 'student' AND is_verified = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getCount()
    {
        $query = "SELECT COUNT(*) as stu_count FROM users WHERE role='student'";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }

    public  function getById($id)
    {
        //get all Instructor from User Table
        $stmt = $this->db->prepare("SELECT * FROM users WHERE uuid = ? AND role = 'student'");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function add($uuid, $name, $email, $password_hash)
    {
        //add student logic 
        $stmt = $this->db->prepare("
                INSERT INTO users(uuid, user_name, email, password_hash, is_verified) 
                VALUES (:uuid, :user_name, :email, :password_hash, :is_verified)
            ");

        $stmt->execute([
            ':uuid' => $uuid,
            ':user_name' => $name,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':is_verified' => true
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        //remove student from db
        $stmt = $this->db->prepare("DELETE FROM users WHERE uuid = ? AND role = 'student'");
        $stmt->execute([$id]);
    }

    public function edit($name, $email, $status, $id)
    {
        //edit Instructor details 
        $stmt = $this->db->prepare("UPDATE users SET user_name = ?, email = ?, is_verified = ? WHERE uuid = ? AND role = 'student'");
        $stmt->execute([$name, $email, $status, $id]);
        return $stmt->rowCount() > 0;
    }
}
