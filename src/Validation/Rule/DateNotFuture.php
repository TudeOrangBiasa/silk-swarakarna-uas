<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

final class DateNotFuture implements Rule
{
    public function __construct(private string $message) {}

    public function validate(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value > date('Y-m-d')) {
            return $this->message;
        }
        return null;
    }
}
