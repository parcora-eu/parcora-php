<?php

declare(strict_types=1);

namespace Parcora\Util;

use DateTimeImmutable;
use Exception;

/**
 * A thin, type-safe reader over a decoded JSON object. Keeps model hydration
 * tidy and free of `mixed` juggling so static analysis stays strict.
 */
final class Data
{
    /** @param array<array-key, mixed> $data */
    private function __construct(private readonly array $data) {}

    /** @param array<array-key, mixed> $data */
    public static function of(array $data): self
    {
        return new self($data);
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->data[$key] ?? null;

        return \is_string($value) ? $value : (\is_scalar($value) && ! \is_bool($value) ? (string) $value : $default);
    }

    public function stringOrNull(string $key): ?string
    {
        $value = $this->data[$key] ?? null;

        return \is_string($value) ? $value : null;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->data[$key] ?? null;

        return \is_int($value) ? $value : (is_numeric($value) ? (int) $value : $default);
    }

    public function intOrNull(string $key): ?int
    {
        $value = $this->data[$key] ?? null;

        return \is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->data[$key] ?? null;

        return \is_bool($value) ? $value : $default;
    }

    public function floatOrNull(string $key): ?float
    {
        $value = $this->data[$key] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    public function dateTimeOrNull(string $key): ?DateTimeImmutable
    {
        $value = $this->stringOrNull($key);

        if ($value === null) {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }

    public function timestampOrNull(string $key): ?DateTimeImmutable
    {
        $value = $this->intOrNull($key);

        return $value === null ? null : (new DateTimeImmutable)->setTimestamp($value);
    }

    /** @return array<array-key, mixed> */
    public function array(string $key): array
    {
        $value = $this->data[$key] ?? null;

        return \is_array($value) ? $value : [];
    }

    /** @return array<string, mixed>|null */
    public function objectOrNull(string $key): ?array
    {
        $value = $this->data[$key] ?? null;

        if (! \is_array($value)) {
            return null;
        }

        $out = [];
        foreach ($value as $k => $v) {
            if (\is_string($k)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    /** @return list<array<array-key, mixed>> */
    public function listOfObjects(string $key): array
    {
        $out = [];
        foreach ($this->array($key) as $item) {
            if (\is_array($item)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /** @return list<string> */
    public function listOfStrings(string $key): array
    {
        $out = [];
        foreach ($this->array($key) as $item) {
            if (\is_string($item)) {
                $out[] = $item;
            }
        }

        return $out;
    }

    /**
     * A backed enum, tolerant of values this SDK version has never heard of.
     *
     * The API's vocabularies are open: carriers get added, statuses get refined,
     * a new tracking milestone appears. Decoding those strictly would make every
     * such addition a breaking change for anyone who has not upgraded — and not
     * a small one, because the failure takes down the whole response rather than
     * the one field. Unrecognised values become the enum's `Unknown` case.
     *
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return T
     */
    public function enum(string $key, string $enum): \BackedEnum
    {
        /** @var T */
        return $enum::tryFrom($this->string($key)) ?? $enum::from('unknown');
    }

    /** @return array<string, string> */
    public function stringMap(string $key): array
    {
        $out = [];
        foreach ($this->array($key) as $k => $v) {
            if (\is_string($k) && \is_string($v)) {
                $out[$k] = $v;
            }
        }

        return $out;
    }
}
