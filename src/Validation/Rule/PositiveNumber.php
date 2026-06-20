<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

final class PositiveNumber implements Rule
{
    public function __construct(private string $message) {}

    public function validate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value <= 0 ? $this->message : null;
    }
}
