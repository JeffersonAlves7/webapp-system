<?php
require_once "Models/Model.php";

class User extends Model
{
    public function login($email, $password)
    {
        $sql = "SELECT * FROM `users` WHERE `email` = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            return $user;
        }

        return null;
    }

    public function register($name, $email, $password)
    {
        $sql = "INSERT INTO `users` (`username`, `email`, `password`) VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }
}
