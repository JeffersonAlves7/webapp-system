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
        // Selecionar todos os controllers
        $sql = "SELECT * FROM `controllers`";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $controllers = [];

        while ($row = $result->fetch_assoc()) {
            $controllers[] = $row;
        }

        if (empty($controllers)) return array();

        if ($group_ID == 1) {
            $permissions = [];
            foreach ($controllers as $controller) {
                $permissions[$controller["name"]] = [
                    "read" => true,
                    "write" => true,
                    "delete" => true,
                    "edit" => true,
                    "controller" => $controller["name"],
                    "ID" => $controller["ID"]
                ];
            }

            return $permissions;
        }

        // Selecionar todas as permissoes do grupo
        $sql = "SELECT 
        `permissions`.`ID`,
        `permissions`.`read`,
        `permissions`.`write`,
        `permissions`.`delete`,
        `permissions`.`edit`,
        `controllers`.`name`,
        `controllers`.`ID` as controller_ID
         FROM `permissions`
        INNER JOIN `controllers` ON `controllers`.`ID` = `permissions`.`controller_ID` 
        WHERE `group_ID` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $group_ID);
        $stmt->execute();

        $result = $stmt->get_result();
        $permissions = [];

        // Se o controller nao tiver permissao, criar uma permissao com read, write e delete como false
        foreach ($controllers as $controller) {
            $permissions[$controller["name"]] = [
                "read" => false,
                "write" => false,
                "delete" => false,
                "edit" => false,
                "controller" => $controller["name"],
                "ID" => $controller["ID"]
            ];
        }

        // Se o controller tiver permissao, atualizar a permissao
        while ($row = $result->fetch_assoc()) {
            $permissions[$row["name"]] = [
                "read" => $row["read"],
                "write" => $row["write"],
                "delete" => $row["delete"],
                "edit" => $row["edit"],
                "controller" => $row["name"],
                "ID" => $row["controller_ID"]
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

    public function updateGroupPermissions($group_ID, $permissions)
    {
        $this->db->beginTransaction();

        try {
            foreach ($permissions as $controller => $permission) {
                $permission = $this->preparePermission($permission);

                $sql = "SELECT * FROM `permissions` WHERE `group_ID` = ? AND `controller_ID` = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ii", $group_ID, $permission["ID"]);
                $stmt->execute();

                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row) {
                    $sql = "UPDATE `permissions` SET `read` = ?, `write` = ?, `delete` = ?, `edit` = ?  WHERE `group_ID` = ?  AND `controller_ID` = ?";
                } else {
                    $sql = "INSERT INTO `permissions` (`read`, `write`, `delete`, `edit`, `group_ID`, `controller_ID`) VALUES (?, ?, ?, ?, ?, ?)";
                }

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("iiiiii", $permission["read"], $permission["write"], $permission["delete"], $permission['edit'], $group_ID, $permission["ID"]);
                $stmt->execute();
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            echo "Erro ao atualizar permissões!";
            throw $e;
        }

        return $stmt->affected_rows > 0;
    }

    private function preparePermission($permission)
    {
        return [
            "read" => isset($permission["read"]) && $permission["read"] ? 1 : 0,
            "write" => isset($permission["write"]) && $permission["write"] ? 1 : 0,
            "delete" => isset($permission["delete"]) && $permission["delete"] ? 1 : 0,
            "edit" => isset($permission["edit"]) && $permission["edit"] ? 1 : 0,
            "ID" => (int) $permission["ID"]
        ];
    }

    public function createGroup($groupName)
    {
        $sql = "INSERT INTO `permission_groups` (`name`) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $groupName);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    public function deleteGroup($group_ID)
    {
        // Se o grupo for o grupo de administradores, nao deletar
        if ($group_ID == 1) {
            return false;
        }

        // Alterar permissao de todos os usuarios do grupo para o grupo NULL
        $sql = "UPDATE `users` SET `group_ID` = NULL WHERE `group_ID` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $group_ID);
        $stmt->execute();

        // Depois deletar o grupo
        $sql = "DELETE FROM `permission_groups` WHERE `ID` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $group_ID);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }
}
