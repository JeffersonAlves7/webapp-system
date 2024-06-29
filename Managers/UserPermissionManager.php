<?php

require_once "Models/User.php";

class Permission
{
    private $read;
    private $write;
    private $delete;
    private $edit;

    public function __construct($read, $write, $delete, $edit)
    {
        $this->read = $read;
        $this->write = $write;
        $this->delete = $delete;
        $this->edit = $edit;
    }

    public function canRead()
    {
        return $this->read;
    }

    public function canWrite()
    {
        return $this->write;
    }

    public function canDelete()
    {
        return $this->delete;
    }

    public function canEdit()
    {
        return $this->edit;
    }
}

class UserPermissionManager
{
    private $permissions;
    private $user;

    public function __construct($userId)
    {
        $userModel = new User();
        $user = $userModel->getUserById($userId);
        $this->user = $user;

        $permissions = $userModel->getGroupsPermissions($this->user["group_ID"]);
        $userPermissions = [];

        foreach ($permissions as $permission) {
            $userPermissions[$permission["controller"]] = new Permission(
                $permission["read"],
                $permission["write"],
                $permission["delete"],
                $permission["edit"],
            );
        }

        $this->permissions = $userPermissions;
    }

    public function controller($name)
    {
        if ($this->user["group_ID"] == 1) {
            return new Permission(true, true, true, true);
        }

        if (isset($this->permissions[$name])) {
            return $this->permissions[$name];
        } else {
            return new Permission(false, false, false, false);
        }
    }
}
