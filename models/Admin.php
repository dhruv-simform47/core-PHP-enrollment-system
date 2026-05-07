<?php
//this class is used by admin only
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
    public  function getAll()
    {
        //get all admin from User Table
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
        //add Admin logic 

        $stmt = $this->db->prepare("
                INSERT INTO users(uuid, user_name, email, password_hash, role, is_verified) 
                VALUES (:uuid, :user_name, :email, :password_hash,'admin', :is_verified)
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
        //remove admin from db
        $stmt = $this->db->prepare("DELETE FROM users WHERE uuid = ? AND role = 'admin'");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function edit($name, $email, $status, $id)
    {
        //edit Admin details 
        $stmt = $this->db->prepare("UPDATE users SET user_name = ?, email = ?, is_verified = ? WHERE uuid = ? AND role = 'admin'");
        $stmt->execute([$name, $email, $status, $id]);
        return $stmt->rowCount() > 0;
    }
}
