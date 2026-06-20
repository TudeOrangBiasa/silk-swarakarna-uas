<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

final class MaxLength implements Rule
{
    public function __construct(private string $message, private int $max) {}

    public function validate(mixed $value): ?string
    {
        if ($value !== null && $value !== '' && mb_strlen((string) $value) > $this->max) {
            return $this->message;
        }
        return null;
    }
}
