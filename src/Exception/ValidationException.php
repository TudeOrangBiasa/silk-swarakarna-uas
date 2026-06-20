<?php

declare(strict_types=1);

namespace Silk\Exception;

use RuntimeException;

/**
 * Validation exception with per-field error messages.
 * Collects and exposes structured errors keyed by field name.
 */
class ValidationException extends RuntimeException
{
    /** @var array<string, string> */
    protected array $errors;

    /**
     * @param array<string, string> $errors Field => message
     * @throws ValidationException
     */
    public function __construct(
        array $errors = [],
        string $message = 'Validasi gagal',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /** @return array<string, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /** @return string|null */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /** @return bool */
    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    public function getFieldErrors(): array
    {
        return $this->errors;
    }
}
