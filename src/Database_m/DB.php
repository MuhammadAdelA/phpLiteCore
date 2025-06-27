<?php

namespace MyApp\Database;

/**
 * @method static MySQL table(string $table)
 * @method static MySQL beginTransaction()
 * @method static MySQL commit()
 * @method static MySQL rollBack()
 */

class DB
{
    private static null|MySQL $instance = null;

    public static function getInstance(): MySQL
    {
        if(!self::$instance) {
            self::$instance = MySQL::get_instance();
        }
        return self::$instance;
    }

    public static function __callStatic(string $method, array $arguments): object
    {
        $instance = self::getInstance();
        return call_user_func_array([$instance, $method], $arguments);
    }

}