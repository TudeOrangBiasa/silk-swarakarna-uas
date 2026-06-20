<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Silk\Exception\ValidationException;

/**
 * Base test case for entity tests.
 * Provides the expectValidationException helper used across entity tests.
 */
abstract class EntityTestCase extends TestCase
{
    /**
     * @param list<string> $expectedFields
     */
    protected function expectValidationException(callable $action, array $expectedFields): void
    {
        try {
            $action();
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            foreach ($expectedFields as $field) {
                $this->assertArrayHasKey($field, $e->getErrors());
            }
        }
    }
}
