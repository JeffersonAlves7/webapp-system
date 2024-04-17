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

        if (!$user) {
            return null;
        }

        if (!$user["active"]) {
            return false;
        }

        if (password_verify($password, $user["password"]))
            return $user;

        return null;
    }

    public function register($name, $email, $password)
    {
        $sql = "INSERT INTO `users` (`username`, `email`, `password`) VALUES (?, ?, ?)";

        $password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function getUserById($id)
    {
        $sql = "SELECT * FROM `users` WHERE `id` = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getGroups()
    {
        $sql = "SELECT * FROM `permission_groups`";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $groups = [];

        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }

        return $groups;
    }

    public function getGroupsPermissions($group_ID)
    {
        $sql = "SELECT 
            `permissions`.`ID`, 
            `permissions`.`read`,
            `permissions`.`write`,
            `permissions`.`delete`,
            `controllers`.`name` AS `controller_name`
        FROM `permissions` 
        INNER JOIN `controllers` ON `controllers`.`ID` = `permissions`.`controller_ID` 
        WHERE `group_ID` = ?";
        $stmt = $this->db->prepare($sql);

        $stmt->bind_param("i", $group_ID);
        $stmt->execute();

        $result = $stmt->get_result();
        $permissions = [];

        while ($row = $result->fetch_assoc()) {
            $permissions[$row["controller_name"]] = [
                "read" => $row["read"],
                "write" => $row["write"],
                "delete" => $row["delete"]
            ];
        }

        return $permissions;
    }

    public function getAllUsers($page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT * FROM `users` LIMIT ?, ?";
        $stmt = $this->db->prepare($sql);

        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();

        $result = $stmt->get_result();
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return [
            "users" => $users,
            "pageCount" => ceil($this->db->query("SELECT COUNT(*) FROM `users`")->fetch_row()[0] / $limit)
        ];
    }

    public function updateUser($id, $types, $properties)
    {
        $sql = "UPDATE `users` SET ";
        $values = [];
        $set = "";

        foreach ($properties as $key => $value) {
            $set .= "`$key` = ?, ";
            $values[] = $value;
        }

        $set = rtrim($set, ", ");
        $sql .= $set . " WHERE `id` = ?";
        $values[] = $id;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types . "i", ...$values);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function deleteUser($id)
    {
        $sql = "DELETE FROM `users` WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }
}
