<?php
//this class is used by admin only
class Instructor
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }
    public  function getAll()
    {
        //get all Instructor from User Table
        $instructorStmt = $this->db->prepare("SELECT * FROM users WHERE role = 'instructor' ORDER BY created_at DESC");
        $instructorStmt->execute();
        return $instructorStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getCount()
    {
        $query = "SELECT COUNT(*) as instructor_count FROM users WHERE role='instructor'";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }
    public  function getById($id)
    {
        //get all Instructor from User Table
        $instructorStmt = $this->db->prepare("SELECT * FROM users WHERE uuid = ? AND role = 'instructor'");
        $instructorStmt->execute([$id]);
        return $instructorStmt->fetch(PDO::FETCH_ASSOC);
    }
    public function add($uuid, $name, $email, $password_hash)
    {
        //add Instructor logic 
        $stmt = $this->db->prepare("
                INSERT INTO users(uuid, user_name, email, password_hash, role, is_verified) 
                VALUES (:uuid, :user_name, :email, :password_hash,'instructor', :is_verified)
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
        //remove Instructor from db
        $stmt = $this->db->prepare("DELETE FROM users WHERE uuid = ? AND role = 'instructor'");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function edit($name, $email, $status, $id)
    {
        //edit Instructor details 
        $stmt = $this->db->prepare("UPDATE users SET user_name = ?, email = ?, is_verified = ? WHERE uuid = ? AND role = 'instructor'");
        $stmt->execute([$name, $email, $status, $id]);
        return $stmt->rowCount() > 0;
    }
}
