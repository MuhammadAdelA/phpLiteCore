<?php

declare(strict_types=1);

namespace PhpLiteCore\Exceptions;

use Exception;

/**
 * Base exception for phpLiteCore
 *
 * All framework-specific exceptions should extend this class
 */
class PhpLiteCoreException extends Exception
{
    /**
     * Additional context data for the exception
     */
    protected array $context = [];

    /**
     * Constructor
     *
     * @param string $message
     * @param int $code
     * @param array $context Additional context data
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, array $context = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the exception context
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set additional context
     *
     * @param array $context
     * @return static
     */
    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }
}
