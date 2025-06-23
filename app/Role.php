<?php

namespace MyApp\app;

use MyApp\app\Database\MySQL;
use const MyApp\__LANG__;
use const MyApp\l_active;
use const MyApp\l_failed_to_delete_from_database;
use const MyApp\l_maximum_characters_is_255;
use const MyApp\l_not_available;
use const MyApp\l_pending;
use const MyApp\l_role_does_not_exists;
use const MyApp\l_role_is_in_use;
use const MyApp\l_role_name_ar_cannot_be_empty;
use const MyApp\l_role_name_en_cannot_be_empty;
use const MyApp\l_super_admin_role_cannot_be_edited_or_deleted;
use const MyApp\l_suspended;

class Role
{
    public int|string $id;
    public string $role_name_ar;
    public string $role_name_en;
    public int|string $staff_member;

    public array $errors_list = [];
    public string $error_m;
    private MySQL $mySQL;

    public function __construct(){
        $this->mySQL = MySQL::get_instance();
    }

    /**
     * @return int
     */
    public function validate(): int
    {
        if (Validate::is_set($this->role_name_ar)) {
            if (!Validate::max255($this->role_name_ar)) {
                $this->errors_list[] = [l_maximum_characters_is_255, "role_name_ar"];
            }
        } else {
            $this->errors_list[] = [l_role_name_ar_cannot_be_empty, "role_name_ar"];
        }

        if (Validate::is_set($this->role_name_en)) {
            if (!Validate::max255($this->role_name_en)) {
                $this->errors_list[] = [l_maximum_characters_is_255, "role_name_en"];
            }
        } else {
            $this->errors_list[] = [l_role_name_en_cannot_be_empty, "role_name_en"];
        }

        return count($this->errors_list);
    }

    /**
     * @param $role_id
     * @return bool|array
     */
    public function get($role_id): bool|array
    {
        global $sqlsrvDB;

        if(!$role = $sqlsrvDB->table('role')->found('id', $role_id)) {
            $this->error_m = l_role_does_not_exists;
            return false;
        }

        return $role;
    }

    /**
     * @return bool|array
     */
    public function get_all(): bool|array
    {
        return $this->mySQL->fetchAll("role");
    }

    /**
     * @param $permission
     * @return bool
     */
    public function allowed($permission): bool
    {
        if(!(new Auth())->has($permission)){
            $this->error_m = sprintf(l_you_dont_have_permission, constant($permission));
            return false;
        }
        return true;
    }

    /**
     * @param string|int $role_id
     * @return string
     */
    public function get_role_name(string|int $role_id): string
    {
        if($role_info = $this->get($role_id))
            return $role_info["role_name_" . __LANG__];
        return l_not_available;
    }

    /**
     * @param string|int $status_id
     * @return string
     */
    public function get_status_name(string|int $status_id): string
    {
        return match ($status_id) {
            "0" => l_pending,
            "1" => l_active,
            "2" => l_suspended,
            default => l_not_available
        };
    }

    /**
     * @param array $post
     * @return bool|int
     */
    public function create(array $post): bool|int
    {
        global $sqlsrvDB, $sess;

        if(!$this->allowed("l_p_add_ProvidedService_")) {
            return false;
        }

        $this->role_name_ar = $post["role_name_ar"] ?? "";
        $this->role_name_en = $post["role_name_en"] ?? "";
        $this->staff_member = (int)isset($post["staff_member"]);

        if($this->validate())
            return false;


        $columns = ["role_name_ar", "role_name_en", "staff_member", "creator", "modifier"];
        $values = [$this->role_name_ar, $this->role_name_en, $this->staff_member, $sess->logged_id, $sess->logged_id];

        if(isset($post["permissions"]) && Validate::is_set($post["permissions"])){
            $columns[] = "permissions";
            $values[] = implode(",", $post["permissions"]);
        }

        return $this->mySQL->table('role')->insert($columns, $values);
    }

    /**
     * @param array $post
     * @return bool|int
     */
    public function update(array $post): bool|int
    {
        global $sess;

        if(!$role = $this->get($post["id"]))
            return false;

        if(!$this->allowed("l_p_modify_ProvidedService_"))
            return false;

        $this->role_name_ar = $post["role_name_ar"] ?? "";
        $this->role_name_en = $post["role_name_en"] ?? "";
        $this->staff_member = (int)isset($post["staff_member"]);

        if($this->validate())
            return false;

        $columns = ["role_name_ar", "role_name_en", "modifier", "modified"];
        $values = [$this->role_name_ar, $this->role_name_en, $sess->logged_id, "NOW()"];

        if(isset($post["permissions"]) && Validate::is_set($post["permissions"])){
            if($post["id"] != 1){
                $columns[] = "staff_member";
                $values[] = $this->staff_member;

                $columns[] = "permissions";
                $values[] = implode(",", $post["permissions"]);
            } else {
                $this->error_m =  l_super_admin_role_cannot_be_edited_or_deleted;
                return false;
            }
        }
        return $this->mySQL->table('role')->where("id", $role["id"])->update($columns, $values);
    }

    /**
     * @param int|string $role_id
     * @return bool
     */
    public function delete(int|string $role_id): bool
    {
        if(!$role = $this->get($role_id))
            return false;

        if(!$this->allowed("l_p_delete_ProvidedService_"))
            return false;

        if($role_id == "1"){
            $this->error_m =  l_super_admin_role_cannot_be_edited_or_deleted;
            return false;
        }

        if ($this->mySQL->table('member')->found('role_id', $role["id"])) {
            $this->error_m = l_role_is_in_use;
            return false;
        }

        $this->errors_list["message"] = l_failed_to_delete_from_database;
        return $this->mySQL->table('role')->where("id", $role_id)->delete();
    }
}