<?php
require_once "Models/Model.php";

class User extends Model
{
    public function login($email, $password)
    {
        $result = $this->db->query("SELECT * FROM `users` WHERE `email` = \"$email\"");

        if ($result) {
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    return $user;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function register($username, $email, $password)
    {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        return $this->db->query("INSERT INTO `users` (
            `username`,
            `email`,
            `password`
        ) VALUES (
            \"$username\",
            \"$email\",
            \"$password_hash\"
        )");
    }

    public function updateSessionToken($user_ID, $session_token)
    {
        $this->db->query("UPDATE `users` SET `session_token` = \"$session_token\" WHERE `ID` = $user_ID");
    }
}
