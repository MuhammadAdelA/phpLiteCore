<?php

namespace MyApp;

class Email
{
    function __construct(){

    }

    /**
     * @param string|int $id
     * @return bool|array
     */
    public function get(string|int $id): bool|array
    {
        global $sqlsrvDB;
        return $sqlsrvDB->where("id", $id)->fetch( );
    }

    /**
     * @return bool|array
     */
    public static function get_all(): bool|array
    {
        global $sqlsrvDB;
        if(!(new Auth())->has("l_p_administrate_Member_")){
            return false;
        }
        return $sqlsrvDB->fetch("member");
    }

    public function create(){

    }

    public function update(){

    }

    public function delete (){

    }
}