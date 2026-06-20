<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

final class PhoneFormat implements Rule
{
    public function __construct(private string $message) {}

    public function validate(mixed $value): ?string
    {
        if ($value !== null && $value !== '' && !preg_match('/^[0-9]{10,15}$/', (string) $value)) {
            return $this->message;
        }
        return null;
    }
}
