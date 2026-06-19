<?php

declare(strict_types=1);

namespace Silk\Exception;

use RuntimeException;

/**
 * Validation exception with per-field error messages.
 *
 * Collects and exposes structured validation errors keyed by field name.
 * Intended for use by entity classes to surface granular validation
 * failures to the presentation layer.
 *
 * Usage:
 *   $e = new ValidationException(['nama' => 'Nama wajib diisi']);
 *   echo $e->getError('nama'); // "Nama wajib diisi"
 *   $e->addError('alamat', 'Alamat wajib diisi');
 *   $errors = $e->getErrors(); // ['nama' => ..., 'alamat' => ...]
 */
class ValidationException extends RuntimeException
{
    /** @var array<string, string> */
    protected array $errors;

    /**
     * @param array<string, string> $errors   Associative array of field => message
     * @param string                $message  Exception message (default: Bahasa Indonesia)
     * @param int                   $code     Exception code
     * @param \Throwable|null       $previous Previous exception for chaining
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

    /**
     * Return all field errors.
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get the error message for a specific field, or null if none.
     *
     * @param string $field Field name
     * @return string|null  Error message, or null if field has no error
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Add a single field error.
     *
     * @param string $field   Field name
     * @param string $message Error message
     */
    public function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Check whether any field errors have been registered.
     *
     * @return bool True if at least one error exists
     */
    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * Alias for {@see getErrors()}.
     *
     * @return array<string, string>
     */
    public function getFieldErrors(): array
    {
        return $this->errors;
    }
}
