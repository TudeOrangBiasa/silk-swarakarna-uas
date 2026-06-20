<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

final class Required implements Rule
{
    public function __construct(private string $message) {}

    public function validate(mixed $value): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && $value === [])) {
            return $this->message;
        }
        return null;
    }
}
