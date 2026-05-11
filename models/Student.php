<?php
class Student
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getAll()
    {
        return $this->db->query("SELECT * FROM users WHERE role = 'student' AND is_verified = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount()
    {
        $query = "SELECT COUNT(*) as stu_count FROM users WHERE role='student'";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE uuid = ? AND role = 'student'");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($uuid, $name, $email, $password_hash)
    {
        try {
            $this->db->beginTransaction();
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
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Student::add Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM users WHERE uuid = ? AND role = 'student'");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Student::delete Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function edit($name, $email, $status, $id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE users SET user_name = ?, email = ?, is_verified = ? WHERE uuid = ? AND role = 'student'");
            $stmt->execute([$name, $email, $status, $id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Student::edit Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }
}