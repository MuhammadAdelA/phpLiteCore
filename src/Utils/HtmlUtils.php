<?php

// File: src/Utils/HtmlUtils.php
declare(strict_types=1);

namespace PhpLiteCore\Utils;

class HtmlUtils
{
    /**
     * Create an HTML unordered list from a string or array of error messages.
     *
     * @param string|string[] $list The error message(s) to list.
     * @return string               HTML string of <ul> with each message in <li>.
     */
    public static function errorList(string|array $list): string
    {
        // Normalize input to array
        $items = is_string($list) ? [$list] : $list;

        // Build list items safely
        $liItems = array_map(
            static fn (string $msg): string => sprintf('<li>%s</li>', htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')),
            $items
        );

        // Wrap in unordered list
        return '<ul>' . implode('', $liItems) . '</ul>';
    }

    /**
     * Get the appropriate float direction based on RTL or LTR context.
     *
     * @return string 'left' for RTL, 'right' for LTR.
     */
    public static function classNameDirectionFix(): string
    {
        // is_rtl() is assumed to be a global helper that returns true for RTL languages.
        return is_rtl() ? 'left' : 'right';
    }

    /**
     * Get the uppercase abbreviation for the current default language.
     *
     * @return string First letter of DEFAULT_LANG constant, uppercased.
     */
    public static function switchNameLang(): string
    {
        // DEFAULT_LANG should be defined, e.g. 'en', 'ar'
        if (! defined('DEFAULT_LANG')) {
            return '';
        }

        return strtoupper(substr((string) DEFAULT_LANG, 0, 1));
    }

    /**
     * Some elements need to be reversed depending on the page direction
     * Reversing an HTML element direction
     * @return string
     */
    public static function reverse_direction(): string
    {
        return is_rtl() ? "ltr" : "rtl";
    }
}
