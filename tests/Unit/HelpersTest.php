<?php

test('e() escapes HTML special characters', function () {
    $result = e('<script>alert("XSS")</script>');
    expect($result)->toBe('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;');
});

test('e() escapes single quotes', function () {
    $result = e("It's a test");
    expect($result)->toBe('It&#039;s a test');
});

test('e() escapes double quotes', function () {
    $result = e('Say "Hello"');
    expect($result)->toBe('Say &quot;Hello&quot;');
});

test('e() escapes ampersands', function () {
    $result = e('Tom & Jerry');
    expect($result)->toBe('Tom &amp; Jerry');
});

test('e() handles null values', function () {
    $result = e(null);
    expect($result)->toBe('');
});

test('e() handles empty strings', function () {
    $result = e('');
    expect($result)->toBe('');
});

test('e() preserves UTF-8 characters', function () {
    $result = e('Hello 世界 🌍');
    expect($result)->toBe('Hello 世界 🌍');
});

test('e() handles complex HTML injection attempts', function () {
    $result = e('<img src=x onerror="alert(1)">');
    expect($result)->toBe('&lt;img src=x onerror=&quot;alert(1)&quot;&gt;');
});
