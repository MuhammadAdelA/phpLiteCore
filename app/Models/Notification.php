<?php

namespace MyApp;

class Notification
{
    public int|string $id;
    public int|string $work_group_id;
    public int|string $member_id;
    public int|string $requested_service_id;
    public string $uri;
    public string $message;

    public array $errors = [];

    public function __construct(){

    }

    /**
     * @param $notification_id
     * @return bool|array
     */
    public function get($notification_id): bool|array
    {
        global $sqlsrvDB;

        if(!$notification = $sqlsrvDB->table('notification')->found('id', $notification_id)) {
            $this->errors["message"] = l_notification_does_not_exists;
            return false;
        }

        return $notification;
    }

    /**
     * @return bool|array
     */
    public static function get_all(): bool|array
    {
        global $sqlsrvDB;
        return $sqlsrvDB->fetchAll("notification");
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

        $this->work_group_id = $post["work_group_id"] ?? 0;
        $this->member_id = $post["member_id"] ?? 0;
        $this->requested_service_id = $post["requested_service_id"] ?? 0;
        $this->message = $post["message"] ?? "";

        $this->uri = "/service-requirements/" . $this->requested_service_id;

        $columns = ["requested_service_id", "uri", "message", "creator"];
        $values = [$this->requested_service_id, $this->uri, $this->message, $sess->logged_id];

        if($this->work_group_id != 0){
            $columns[] = "work_group_id";
            $values[] = $this->work_group_id;
        }

        if($this->member_id != 0){
            $columns[] = "member_id";
            $values[] = $this->member_id;
        }

        return $sqlsrvDB->table('notification')->insert($columns, $values);
    }

    /**
     * @param string|int $notification_id
     * @return int
     */
    public function seen(string|int $notification_id): int
    {
        global $sqlsrvDB;
        return $sqlsrvDB->where("id", $notification_id)->table('notification')->update("seen", 1);
    }

    public function count_unseen(){
        global $sqlsrvDB, $sess;

    }
}