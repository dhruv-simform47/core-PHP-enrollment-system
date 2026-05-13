<?php
class Instructor
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getAll()
    {
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

    public function getById($id)
    {
        $instructorStmt = $this->db->prepare("SELECT * FROM users WHERE uuid = ? AND role = 'instructor'");
        $instructorStmt->execute([$id]);
        return $instructorStmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($uuid, $name, $email, $password_hash)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO users(uuid, user_name, email, password_hash, role, is_verified) 
                VALUES (:uuid, :user_name, :email, :password_hash, 'instructor', :is_verified)
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
            error_log(date('[Y-m-d H:i:s] ') . "Instructor::add Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM users WHERE uuid = ? AND role = 'instructor'");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Instructor::delete Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function edit($name, $email, $status, $id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE users SET user_name = ?, email = ?, is_verified = ? WHERE uuid = ? AND role = 'instructor'");
            $stmt->execute([$name, $email, $status, $id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Instructor::edit Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }
}