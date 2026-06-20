<?php

declare(strict_types=1);

namespace Silk\Validation;

use Silk\Exception\ValidationException;
use Silk\Validation\Rule\Rule;

/** Run validation rules against data, throws ValidationException on first error per field. */
final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, list<Rule>> $rules
     * @throws ValidationException
     */
    public function validate(array $data, array $rules): void
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            foreach ($fieldRules as $rule) {
                $error = $rule->validate($value);
                if ($error !== null) {
                    $errors[$field] = $error;
                    break; // first error per field
                }
            }
        }
        if ($errors !== []) {
            throw new ValidationException($errors);
        }
    }
}
