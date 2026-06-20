<?php

declare(strict_types=1);

namespace Silk\Validation\Rule;

interface Rule
{
    /** @return string|null null if valid, error message if invalid */
    public function validate(mixed $value): ?string;
}
