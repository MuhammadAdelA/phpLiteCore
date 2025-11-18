<?php

declare(strict_types=1);

namespace PhpLiteCore\Utils;

class JsonUtils
{
    /**
     * Generate a JSON-encoded feedback structure.
     *
     * @param string $message The feedback message content.
     * @param string $type    The feedback type (e.g., 'info', 'success', 'error').
     * @param string $title   Optional title for the feedback.
     * @return string         A JSON string with keys: type, message, title.
     */
    public static function feedback(string $message, string $type = 'info', string $title = ''): string
    {
        $data = [
            'type' => $type,
            'message' => $message,
            'title' => $title,
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
