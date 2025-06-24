<?php
namespace PhpLiteCore\Utils;

class TextUtils
{
    /**
     * Normalize Arabic letters in a search term to regex classes
     * to allow flexible matching of common variants.
     *
     * @param string $term The original search term.
     * @return string The term with variant letters replaced by regex classes.
     */
    public static function normalizeSearchTerm(string $term): string
    {
        $map = [
            'ى' => '[ىي]',
            'ي' => '[ىي]',
            'ة' => '[هة]',
            'ه' => '[هة]',
            'ؤ' => '[ؤو]',
            'و' => '[ؤو]',
            'ذ' => '[ذز]',
            'ز' => '[ذز]',
            'ا' => '[ااأإآٱ]',
            'أ' => '[ااأإآٱ]',
            'إ' => '[ااأإآٱ]',
            'آ' => '[ااأإآٱ]',
            'ٱ' => '[ااأإآٱ]',
        ];

        return strtr($term, $map);
    }

    /**
     * Normalize English letters in a search term to regex classes
     * to allow flexible matching of accent and case variants.
     *
     * @param string $term The original search term.
     * @return string The term with accented and uppercase variants replaced by regex classes.
     */
    public static function normalizeEnglishSearchTerm(string $term): string
    {
        $map = [
            'a' => '[aàáâäãåā]',
            'A' => '[AÀÁÂÄÃÅĀ]',
            'e' => '[eèéêëē]',
            'E' => '[EÈÉÊËĒ]',
            'i' => '[iìíîïī]',
            'I' => '[IÌÍÎÏĪ]',
            'o' => '[oòóôöõō]',
            'O' => '[OÒÓÔÖÕŌ]',
            'u' => '[uùúûüū]',
            'U' => '[UÙÚÛÜŪ]',
            'c' => '[cç]',
            'C' => '[CÇ]',
            'n' => '[nñ]',
            'N' => '[NÑ]',
            'y' => '[yÿ]',
            'Y' => '[YŸ]',
        ];

        return strtr($term, $map);
    }
}
