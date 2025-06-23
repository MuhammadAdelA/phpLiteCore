<?php

namespace MyApp;

class SimpleCrud
{
    public int|string $id;
    public string $business_type_ar;
    public string $business_type_en;

    public array $errors = [];

    public function __construct(){

    }

    /**
     * @return int
     */
    public function validate(): int
    {
        if (Validate::is_set($this->business_type_ar)) {
            if (!Validate::max255($this->business_type_ar)) {
                $this->errors["business_type_ar"] = l_maximum_characters_is_255;
            }
        } else {
            $this->errors["business_type_ar"] = l_business_type_ar_cannot_be_empty;
        }

        if (Validate::is_set($this->business_type_en)) {
            if (!Validate::max255($this->business_type_en)) {
                $this->errors["business_type_en"] = l_maximum_characters_is_255;
            }
        } else {
            $this->errors["business_type_en"] = l_business_type_en_cannot_be_empty;
        }

        return count($this->errors);
    }

    /**
     * @param $business_type_id
     * @return bool|array
     */
    public function get($business_type_id): bool|array
    {
        global $sqlsrvDB;

        if(!$business_type = $sqlsrvDB->table('business_type')->found('id', $business_type_id)) {
            $this->errors["message"] = l_business_type_does_not_exists;
            return false;
        }

        return $business_type;
    }

    /**
     * @return bool|array
     */
    public static function get_all(): bool|array
    {
        global $sqlsrvDB;
        return $sqlsrvDB->fetchAll("business_type");
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function allowed(string $permission): bool
    {
        if(!(new Auth())->has($permission)){
            $this->errors["message"] = sprintf(l_you_dont_have_permission, constant($permission));
            return false;
        }
        return true;
    }

    /**
     * @param array $post
     * @return bool|int
     */
    public function create(array $post): bool|int
    {
        global $sqlsrvDB, $sess;

        $this->business_type_ar = $post["business_type_ar"] ?? "";
        $this->business_type_en = $post["business_type_en"] ?? "";

        if(!$this->allowed("l_p_add_BusinessType_")) {
            return false;
        }

        if($this->validate()){
            return false;
        }

        $columns = ["business_type_ar", "business_type_en", "creator", "modifier"];
        $values = [$this->business_type_ar, $this->business_type_en, $sess->logged_id, $sess->logged_id];

        return $sqlsrvDB->table('business_type')->insert($columns, $values);
    }

    /**
     * @param array $post
     * @return bool|int
     */
    public function update(array $post): bool|int
    {
        global $sqlsrvDB, $sess;

        if(!$this->allowed("l_p_modify_BusinessType_")) {
            return false;
        }

        $this->business_type_ar = $post["business_type_ar"] ?? "";
        $this->business_type_en = $post["business_type_en"] ?? "";

        if($this->validate()){
            return false;
        }

        if(!$business_type = $this->get($post["id"])){
            return false;
        }


        $columns = ["business_type_ar", "business_type_en", "modifier", "modified"];
        $values = [$this->business_type_ar, $this->business_type_en, $sess->logged_id, "NOW()"];

        return $sqlsrvDB->where("id", $business_type["id"])->table('business_type')->update($columns, $values);
    }

    /**
     * @param int|string $business_type_id
     * @return bool
     */
    public function delete(int|string $business_type_id): bool
    {
        global $sqlsrvDB;

        if(!$this->allowed("l_p_delete_BusinessType_")) {
            return false;
        }

        if(!$business_type = $this->get($business_type_id)){
            return false;
        }

        if ($sqlsrvDB->table('company')->found('business_type_id', $business_type["id"])) {
            $this->errors["message"] = l_business_type_is_in_use;
            return false;
        }

        $this->errors["message"] = l_failed_to_delete_from_database;
        return $sqlsrvDB->where("id", $business_type_id)->table('business_type')->delete();
    }
}