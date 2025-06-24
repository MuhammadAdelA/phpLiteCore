<?php

use PhpLiteCore\Utils\TextUtils;

it('normalizes Arabic search terms', function () {
    // Arrange & Act
    $result1 = TextUtils::normalizeSearchTerm('كتاب');
    $result2 = TextUtils::normalizeSearchTerm('مدرسة');
    $result3 = TextUtils::normalizeSearchTerm('توجيه');

    // Assert
    expect($result1)->toBe('كت[ااأإآٱ]ب')
        ->and($result2)->toBe('مدرس[هة]')
        ->and($result3)->toBe('توج[ىي][هة]');
});

it('normalizes English search terms', function () {
    $result1 = TextUtils::normalizeEnglishSearchTerm('cafe');
    $result2 = TextUtils::normalizeEnglishSearchTerm('naïve');

    expect($result1)->toBe('[cç][aàáâäãåā]f[eèéêëē]')
        ->and($result2)->toBe('n[aàáâäãåā][ï][v][eèéêëē]');
    // ï is not mapped, remains unchanged
});
