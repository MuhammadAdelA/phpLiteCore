<?php

declare(strict_types=1);

namespace PhpLiteCore\Validation\Exceptions;

use Exception;

/**
 * Exception thrown when data validation fails.
 */
class ValidationException extends Exception
{
    /**
     * The array of validation errors.
     * @var array
     */
    protected array $errors;

    /**
     * ValidationException constructor.
     *
     * @param array $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('The given data was invalid.');
    }

    /**
     * Get the validation errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
