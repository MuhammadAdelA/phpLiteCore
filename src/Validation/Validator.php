<?php

declare(strict_types=1);

namespace PhpLiteCore\Validation;

use PhpLiteCore\Lang\Translator; // Import the Translator class
use PhpLiteCore\Validation\Exceptions\ValidationException;

class Validator
{
    /**
     * The data to be validated.
     * @var array
     */
    protected array $data;

    /**
     * The validation rules.
     * @var array
     */
    protected array $rules;

    /**
     * The validation error messages.
     * @var array
     */
    protected array $errors = [];

    /**
     * The static Translator instance.
     * @var Translator
     */
    protected static Translator $translator;

    /**
     * Validator constructor.
     *
     * @param array $data The data to validate (e.g., $_POST).
     * @param array $rules The validation rules.
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Set the Translator instance to be used for validation messages.
     * This is called once by the Application during bootstrap.
     *
     * @param Translator $translator The translator service instance.
     * @return void
     */
    public static function setTranslator(Translator $translator): void
    {
        static::$translator = $translator;
    }

    /**
     * Static factory method to create and run the validator.
     *
     * @param array $data
     * @param array $rules
     * @return array The validated data.
     * @throws ValidationException
     */
    public static function validate(array $data, array $rules): array
    {
        $validator = new self($data, $rules);
        $validator->run();

        if ($validator->fails()) {
            throw new ValidationException($validator->getErrors());
        }

        return $validator->getValidatedData();
    }

    /**
     * Run the validation process.
     *
     * @return void
     */
    public function run(): void
    {
        foreach ($this->rules as $field => $fieldRules) {
            $rulesArray = explode('|', $fieldRules);
            $value = $this->data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
    }

    /**
     * Apply a single validation rule to a field.
     *
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @return void
     */
    protected function applyRule(string $field, mixed $value, string $rule): void
    {
        // Example: rule is "min:8"
        [$ruleName, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

        // Use the injected translator to get error messages from 'validation.php' files.
        // This adheres to Constitution (Sec 1.5).
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, static::$translator->get('validation.required', ['field' => $field]));
                }

                break;

            case 'email':
                if (! empty($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, static::$translator->get('validation.email', ['field' => $field]));
                }

                break;

            case 'min':
                if (! empty($value) && mb_strlen((string)$value) < (int)$parameter) {
                    $this->addError(
                        $field,
                        static::$translator->get('validation.min', ['field' => $field, 'value' => $parameter])
                    );
                }

                break;

                // Add more rules here (max, numeric, etc.)
        }
    }

    /**
     * Add an error message for a field.
     *
     * @param string $field
     * @param string $message
     * @return void
     */
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Check if validation failed.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return ! empty($this->errors);
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

    /**
     * Get only the data that was specified in the validation rules.
     *
     * @return array
     */
    public function getValidatedData(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }
}
