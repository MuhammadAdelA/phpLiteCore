<?php

namespace MyApp;

use DateTime;

class Validate
{
    public static function is_set(string|array $value): bool
    {
        if(is_array($value))
            return count($value) > 0;
        return (bool)strlen($value);
    }

    /**
     *  Valid for varchar 255
     *
     * @param string $value
     * @return bool
     */
    public static function max255(string $value): bool
    {
        return strlen($value) <= 255;
    }


    /**
     * Full name validation
     * @param string $value
     * @return bool
     */
    public static function full_name(string $value): bool
    {
        return (preg_match("/^\pL+( \pL+)+$/u", $value));
    }

    /**
     * Numbers only validation
     *
     * @param string $value
     * @return bool
     */
    public static function number(string $value): bool
    {
        return (preg_match("/^[0-9]+$/", $value));
    }

    /**
     * Valid phone number
     * @param string $value
     * @return bool
     */
    public static function phone(string $value): bool
    {
        return (preg_match("/^[0-9]{4,20}$/", $value));
    }

    /**
     * Email validation
     *
     * @param string $value
     * @return bool
     */
    public static function email(string $value): bool
    {
        return (preg_match("/^[A-Z0-9]+([A-Z0-9\._-])*@[A-Z0-9]+([A-Z0-9\-\.])*\.[A-Z]+$/i", $value));
    }

    /**
     * Domain validation
     *
     * @param string $value
     * @return bool
     */
    public static function domain(string $value): bool
    {
        return (preg_match("/(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{0,62}[a-zA-Z0-9]\.)+[a-zA-Z]{2,63}$)/i", $value));
    }

    /**
     * ip validation
     *
     * @param string $value
     * @return bool
     */
    public static function ip(string $value): bool
    {
        return (preg_match("/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/i", $value));
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function date_val(string $value): bool
    {
        $value  = explode('-', $value);
        if (count($value) != 3) {
            return false;
        }
        return checkdate($value[1], $value[2], $value[0]);
    }

    public static function valid_date(string $date, string $format = 'Y-m-d'): bool
    {
        $dt = DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) === $date;
    }

    /**
     * ip validation
     *
     * @param string $value
     * @return bool
     */
    public static function host(string $value): bool
    {
        return static::domain($value) || static::ip($value);
    }

    /**
     * port validation
     *
     * @param string $value
     * @return bool
     */
    public static function port(string $value): bool
    {
        return (preg_match("/^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$/i", $value));
    }

    /**
     * Password validation
     *
     * @param string $value
     * @return bool
     */
    public static function password(string $value): bool
    {
        /** *********************************
         * Password Validation criteria
         * *********************************
         * - Grater than or equal 8 and less than or equal 30
         * - Must have at least 1 Number
         * - Must have at least 1 Lowercase letter
         * - Must have at least 1 Uppercase letter
         * - Must have at least 1 Symbol
         * - Could accept ALT characters
         */
        //return (preg_match("/^(?:(?=.*?\p{N})(?=.*?[\p{S}\p{P} ])(?=.*?\p{Lu})(?=.*?\p{Ll}))[^\p{C}]{8,30}$/", $value));

        /** *********************************
         * Password Validation criteria
         * *********************************
         * - Grater than or equal 8 and less than or equal 30
         * - Must have at least 1 Number
         * - Must have at least 1 Lowercase letter
         * - Must have at least 1 Uppercase letter
         * - Could accept ALT characters
         */
        return (preg_match("/^(?:(?=.*?\p{N})(?=.*?)(?=.*?\p{Lu})(?=.*?\p{Ll}))[^\p{C}]{8,30}$/", $value));

    }

    /**
     * Username validation
     *
     * @param string $value
     * @return bool
     */
    public static function username(string $value): bool
    {
        /* *********************************
         * Password Validation criteria
         * *********************************
         * - Grater than or equal 8 and less than or equal 20
         * - Can't start with underscore (_) nor dot (.)
         * - Can't accept multi underscores nor dots nor companion between both
         * - Can't accept underscores or dots at the end
         * - accept only english letters or numbers and dots and underscores
         * - Could be an email address
         * - Could be lowercase or uppercase letters
         */
        return (preg_match("/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[A-Z0-9._]+(?<![_.])$/i", $value) || static::email($value));
    }

    /**
     * Min & Max validation
     *
     * @param string $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function str_length(string $value, int $min = 0, int $max = 0): bool
    {
        return strlen($value) < $min or strlen($value) > $max;
    }
}