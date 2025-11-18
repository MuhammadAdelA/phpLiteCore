<?php

declare(strict_types=1);

namespace PhpLiteCore\Utils;

use DateTime;

/**
 * Simple PSR-3 compatible Logger
 *
 * Provides basic logging functionality with support for different log levels
 * and file-based storage.
 */
class Logger
{
    /**
     * Log levels as defined by PSR-3
     */
    public const EMERGENCY = 'emergency';
    public const ALERT = 'alert';
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const INFO = 'info';
    public const DEBUG = 'debug';

    /**
     * @var string The directory where log files are stored
     */
    private string $logDirectory;

    /**
     * @var string The current log file name
     */
    private string $logFile;

    /**
     * @var string The minimum log level to record
     */
    private string $minLevel;

    /**
     * @var array Log level priorities (higher = more severe)
     */
    private const LEVELS = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::NOTICE => 2,
        self::WARNING => 3,
        self::ERROR => 4,
        self::CRITICAL => 5,
        self::ALERT => 6,
        self::EMERGENCY => 7,
    ];

    /**
     * Constructor
     *
     * @param string $logDirectory The directory where log files will be stored
     * @param string $minLevel The minimum log level to record (default: DEBUG)
     */
    public function __construct(string $logDirectory = '', string $minLevel = self::DEBUG)
    {
        $this->logDirectory = $logDirectory ?: (defined('PHPLITECORE_ROOT') ? PHPLITECORE_ROOT . 'storage/logs' : __DIR__ . '/../../storage/logs');
        $this->minLevel = $minLevel;

        // Create log directory if it doesn't exist
        if (! is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0755, true);
        }

        // Set log file name based on current date
        $this->logFile = $this->logDirectory . '/' . date('Y-m-d') . '.log';
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Check if this level should be logged
        if (! $this->shouldLog($level)) {
            return;
        }

        // Interpolate context values into message placeholders
        $message = $this->interpolate($message, $context);

        // Format the log entry
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        $logEntry = "[{$timestamp}] {$levelUpper}: {$message}";

        // Add context if present
        if (! empty($context)) {
            $logEntry .= ' ' . json_encode($context);
        }

        $logEntry .= PHP_EOL;

        // Write to log file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    private function interpolate(string $message, array $context): string
    {
        // Build a replacement array with braces around the context keys
        $replace = [];

        foreach ($context as $key => $val) {
            // Check that the value can be cast to string
            if (! is_array($val) && (! is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // Interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * Check if a level should be logged based on the minimum level
     *
     * @param string $level
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        $levelPriority = self::LEVELS[$level] ?? 0;
        $minPriority = self::LEVELS[$this->minLevel] ?? 0;

        return $levelPriority >= $minPriority;
    }

    /**
     * Get the current log file path
     *
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * Set the minimum log level
     *
     * @param string $level
     * @return void
     */
    public function setMinLevel(string $level): void
    {
        if (isset(self::LEVELS[$level])) {
            $this->minLevel = $level;
        }
    }
}
