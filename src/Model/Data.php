<?php

declare(strict_types=1);

namespace Xelyos\MailRp\Model;

use Xelyos\MailRp\Exception\UnexpectedResponseException;

/**
 * Typed reads on the decoded JSON of a response.
 *
 * @internal
 */
final class Data
{
    /** @param array<mixed> $data */
    public static function string(array $data, string $key): string
    {
        $value = self::required($data, $key);
        if (!is_string($value)) {
            throw self::invalid($key, 'a string', $value);
        }

        return $value;
    }

    /** @param array<mixed> $data */
    public static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if (null !== $value && !is_string($value)) {
            throw self::invalid($key, 'a string or null', $value);
        }

        return $value;
    }

    /** @param array<mixed> $data */
    public static function bool(array $data, string $key): bool
    {
        $value = self::required($data, $key);
        if (!is_bool($value)) {
            throw self::invalid($key, 'a boolean', $value);
        }

        return $value;
    }

    /** @param array<mixed> $data */
    public static function int(array $data, string $key): int
    {
        $value = self::required($data, $key);
        if (!is_int($value)) {
            throw self::invalid($key, 'an integer', $value);
        }

        return $value;
    }

    /** @param array<mixed> $data */
    public static function date(array $data, string $key): \DateTimeImmutable
    {
        return self::nullableDate($data, $key) ?? throw self::invalid($key, 'a date', null);
    }

    /** @param array<mixed> $data */
    public static function nullableDate(array $data, string $key): ?\DateTimeImmutable
    {
        $value = self::nullableString($data, $key);
        if (null === $value) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw self::invalid($key, 'a date', $value);
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function array(array $data, string $key): array
    {
        $value = self::required($data, $key);
        if (!is_array($value)) {
            throw self::invalid($key, 'an object or a list', $value);
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    public static function nullableArray(array $data, string $key): ?array
    {
        $value = $data[$key] ?? null;
        if (null !== $value && !is_array($value)) {
            throw self::invalid($key, 'an object, a list or null', $value);
        }

        return $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return list<string>
     */
    public static function stringList(array $data, string $key): array
    {
        $list = [];
        foreach (self::array($data, $key) as $item) {
            if (!is_string($item)) {
                throw self::invalid($key, 'a list of strings', $item);
            }
            $list[] = $item;
        }

        return $list;
    }

    /** @param array<mixed> $data */
    private static function required(array $data, string $key): mixed
    {
        if (!array_key_exists($key, $data)) {
            throw new UnexpectedResponseException(sprintf('Field "%s" is missing in the response of the Mail RP API', $key));
        }

        return $data[$key];
    }

    private static function invalid(string $key, string $expected, mixed $value): UnexpectedResponseException
    {
        return new UnexpectedResponseException(sprintf(
            'Field "%s" of the Mail RP API response should be %s, got %s',
            $key,
            $expected,
            get_debug_type($value)
        ));
    }
}
