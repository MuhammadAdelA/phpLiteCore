<?php
declare(strict_types=1);

use PhpLiteCore\Utils\StringUtils;

$tests = [
    [
        'description'    => 'Default (length=12, strength=100%, no separator)',
        'length'         => 12,
        'strength'       => 80,
        'separator'      => '',
        'availableSets'  => 'luds',
    ],
    [
        'description'    => 'Medium strength (length=12, strength=75%, sep="-")',
        'length'         => 12,
        'strength'       => 75,
        'separator'      => '-',
        'availableSets'  => 'luds',
    ],
    [
        'description'    => 'Low strength (length=12, strength=50%, sep=":")',
        'length'         => 12,
        'strength'       => 50,
        'separator'      => ':',
        'availableSets'  => 'luds',
    ],
    [
        'description'    => 'Digits+lower only (length=8, strength=100%, no sep)',
        'length'         => 8,
        'strength'       => 100,
        'separator'      => '',
        'availableSets'  => 'ld',
    ],
    [
        'description'    => 'Specials only (length=6, strength=100%, sep=" ")',
        'length'         => 6,
        'strength'       => 100,
        'separator'      => ' ',
        'availableSets'  => 's',
    ],
];

foreach ($tests as $t) {
    echo "=== {$t['description']} ===\n";

    for ($i = 0; $i < 3; $i++) {
        try {
            $pwd = StringUtils::generateStrongPassword(
                $t['length'],
                $t['strength'],
                $t['separator'],
                $t['availableSets']
            );
        } catch (\Random\RandomException $e) {

        }
        echo "  Test ".($i+1).": $pwd\n";
    }
    echo "\n";
}
