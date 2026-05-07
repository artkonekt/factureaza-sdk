<?php

declare(strict_types=1);

/**
 * Contains the SuperTinyArrayValidator class.
 *
 * @copyright   Copyright (c) 2022 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2022-10-06
 *
 */

namespace Konekt\Factureaza\Validation;

use Konekt\Factureaza\Exceptions\InvalidInvoiceItemException;
use Konekt\Factureaza\Exceptions\ValidationException;
use Konekt\Factureaza\Exceptions\ValidationResult;
use Stringable;

class SuperTinyArrayValidator
{
    private string $exceptionClass = ValidationException::class;

    public function __construct(
        private readonly string $noun,
    ) {
    }

    public static function createFor(string $noun): SuperTinyArrayValidator
    {
        return new self($noun);
    }

    /**
     * @throws ValidationException|InvalidInvoiceItemException
     */
    public function validate(array $schema, array $data): array
    {
        $result = [];
        foreach ($schema as $field => $rule) {
            [$method, $params] = $this->methodFor($rule);

            $validatedValue = call_user_func_array([$this, $method], array_merge($params, ['value' => $data[$field] ?? null]));
            if ($validatedValue instanceof ValidationResult && $validatedValue->isFailed()) {
                $this->throwException($field, $this->invalidFieldAsString($data[$field] ?? null));
            }

            $result[$field] = $validatedValue;
        }

        return $result;
    }

    public function onErrorThrow(string $exceptionClass): self
    {
        $this->exceptionClass = $exceptionClass;

        return $this;
    }

    private function asString(mixed $value): string|ValidationResult
    {
        if (is_string($value)) {
            return $value;
        } elseif ($value instanceof Stringable) {
            return $value->__toString();
        }

        return ValidationResult::FAILED();
    }

    private function asOptionalString(mixed $value, ?string $default = null): string|null|ValidationResult
    {
        if (null === $value) {
            return $default;
        }

        return $this->asString($value);
    }

    private function asOptionalBool(mixed $value, bool $default = false): bool|null|ValidationResult
    {
        if (null === $value) {
            return $default;
        }

        return $this->asBool($value);
    }

    private function asBool(mixed $value): bool|ValidationResult
    {
        if (is_bool($value)) {
            return $value;
        } elseif (0 === $value || 'false' === $value || 'FALSE' === $value) {
            return false;
        } elseif (1 === $value || 'true' === $value || 'TRUE' === $value) {
            return true;
        }

        return ValidationResult::FAILED();
    }

    private function asNumber(mixed $value): float|int|ValidationResult
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        } elseif (is_numeric($value)) {
            return floatval($value);
        }

        return ValidationResult::FAILED();
    }

    private function asOptionalNumber(mixed $value, float|int|null $default = null): float|int|null|ValidationResult
    {
        if (null === $value) {
            return $default;
        }

        return $this->asNumber($value);
    }

    private function throwException(string $field, string $value): void
    {
        $class = $this->exceptionClass;

        throw new $class(
            sprintf('The %s `%s` field value (`%s`) is invalid', $this->noun, $field, $value)
        );
    }

    /**
     * @param string $rule
     * @return array{0: string, 1:array}
     */
    private function methodFor(string $rule): array
    {
        $parts = explode(':', $rule);

        $type = $parts[0];
        if (str_ends_with($type, '*')) {
            $method = 'as' . ucfirst(str_replace('*', '', $type));
        } else {
            $method = 'asOptional' . ucfirst($type);
        }

        $parameters = [];
        if (isset($parts[1])) {
            parse_str($parts[1], $parameters);
        }

        return [$method, $parameters];
    }

    private function invalidFieldAsString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_object($value)) {
            if ($value instanceof Stringable) {
                return 'object ' . get_class($value);
            }
        }

        return (string) ($value ?? 'NULL');
    }
}
