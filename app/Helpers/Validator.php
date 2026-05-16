<?php

declare(strict_types=1);

namespace App\Helpers;

class Validator
{
    private array $errors = [];

    public function required(string $field, mixed $value): static
    {
        if ($value === null || trim((string) $value) === '') {
            $this->errors[$field] ??= ucfirst($field) . ' is required.';
        }
        return $this;
    }

    public function email(string $field, string $value): static
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] ??= 'Invalid email address.';
        }
        return $this;
    }

    public function minLength(string $field, string $value, int $min): static
    {
        if (mb_strlen($value) < $min) {
            $this->errors[$field] ??= ucfirst($field) . " must be at least {$min} characters.";
        }
        return $this;
    }

    public function maxLength(string $field, string $value, int $max): static
    {
        if (mb_strlen($value) > $max) {
            $this->errors[$field] ??= ucfirst($field) . " cannot exceed {$max} characters.";
        }
        return $this;
    }

    public function alphanumericUnderscore(string $field, string $value): static
    {
        if ($value !== '' && !preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            $this->errors[$field] ??= ucfirst($field) . ' may only contain letters, numbers, and underscores.';
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
