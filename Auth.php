<?php

namespace MyApp;

use MyApp\Database\MySQL;

class Auth
{
    public int $role_id;
    public string $error_m;
    public array $errors_list;
    private MySQL $mySQL;
    public function __construct()
    {
        $this->mySQL = MySQL::get_instance();
    }

    /**
     * Get all permissions
     *
     * @param string $type
     * @return bool|array
     */
    public function get_all_permissions(string $type = "array"): bool|array
    {
        if(!$permissions = $this->mySQL->table('role')->where("id", "1")->getThis("permissions")){
            $this->error_m = l_no_permissions_available;
            return false;
        }
        if($type == "array")
            return explode(",", $permissions);

        return $permissions;
    }

    /**
     * @return array
     */
    public function get_modules_privileges(): array
    {
        $modules_codes = [];
        $modules_permissions = [];
        $modules = $this->mySQL->fetchAll("module");

        $all_permissions = $this->get_all_permissions();

        foreach ($modules as $module)
            $modules_codes[] = $module["code"];

        if($all_permissions && $modules_codes){
            foreach ($all_permissions as $permission)
                foreach ($modules_codes as $code)
                    if(str_contains($permission, $code))
                        $modules_permissions[$code][] = $permission;
        }

        return $modules_permissions;
    }

    /**
     * Get the current member role
     *
     * @param string $member_id
     * @return int
     */
    public function role_id(string $member_id = "0"): int
    {
        global $sess;
        return $this->role_id = $this->mySQL->table('member')
            ->where("id", ($member_id ? $member_id : $sess->logged_id))
            ->getThis("role_id");
    }

    /**
     * Get the current member privileges
     *
     * @param int $role_id
     * @return bool|string
     */
    public function privileges(int $role_id = 0): bool|string
    {
        $role = $role_id != 0 ? $role_id : $this->role_id();
        return $role ? $this->mySQL->table('role')
            ->where("id", $role)
            ->getThis("permissions") : false;
    }

    /**
     * Member has the privilege to do so
     *
     * @param $privilege
     * @return bool
     */
    public function has($privilege): bool
    {
        $privileges = $this->privileges();
        return $privileges && in_array(
            $privilege,
            explode(",", $privileges
            )
        );
    }

    public function hooked($id): bool
    {
        return true;
    }

}
