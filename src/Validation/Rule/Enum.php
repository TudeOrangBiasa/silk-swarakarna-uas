<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

final class Enum implements Rule
{
    /** @param list<string> $allowed */
    public function __construct(private array $allowed, private string $message) {}

    public function validate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (!in_array($value, $this->allowed, true)) {
            return $this->message;
        }
        return null;
    }
}
