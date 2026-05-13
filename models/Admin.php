<?php
class Admin
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE uuid = ? AND role = 'admin'");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT uuid, user_name, email, is_verified, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount()
    {
        $query = "SELECT COUNT(*) as admin_count FROM users WHERE role='admin'";
        $stmt = $this->db->query($query);
        return $stmt->fetchColumn();
    }

    public function add($uuid, $name, $email, $password_hash)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                INSERT INTO users(uuid, user_name, email, password_hash, role, is_verified) 
                VALUES (:uuid, :user_name, :email, :password_hash, 'admin', :is_verified)
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
            error_log(date('[Y-m-d H:i:s] ') . "Admin::add Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("DELETE FROM users WHERE uuid = ? AND role = 'admin'");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Admin::delete Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }

    public function edit($name, $email, $status, $id)
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("UPDATE users SET user_name = ?, email = ?, is_verified = ? WHERE uuid = ? AND role = 'admin'");
            $stmt->execute([$name, $email, $status, $id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log(date('[Y-m-d H:i:s] ') . "Admin::edit Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/error.log');
            return false;
        }
    }
}