<?php

namespace Philly\Collection;

use InvalidArgumentException;
use Philly\Collection\Contract\GenericMap;
use Philly\Support\Contract\HashGenerator;
use Philly\Support\SplObjectIdHashGenerator;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements GenericMap<TKey, TValue>
 */
class HashMap implements GenericMap
{
    /** @var array<array-key, TValue> */
    private array $values = [];

    private HashGenerator $hashGenerator;

    /**
     * @param iterable<TKey, TValue> $items
     */
    public function __construct(iterable $items = [], ?HashGenerator $hashGenerator = null)
    {
        $this->hashGenerator = $hashGenerator ?? new SplObjectIdHashGenerator();

        foreach ($items as $key => $value) {
            $this->put($key, $value);
        }
    }

    public function put(mixed $key, mixed $value): void
    {
        $mapKey = $this->getHashForKey($key);

        $this->values[$mapKey] = $value;
    }

    public function get(mixed $key): mixed
    {
        $internalKey = $this->getHashForKey($key);

        if (!array_key_exists($internalKey, $this->values)) {
            throw new InvalidOffsetException("The given key was either already destroyed or does not exist.");
        }

        return $this->values[$internalKey];
    }

    /**
     * @param TKey $key
     * @return array-key
     */
    private function getHashForKey(mixed $key): int|string
    {
        if (is_int($key) || is_string($key)) {
            return $key;
        }

        if (is_object($key)) {
            return $this->hashGenerator->generateHash($key);
        }

        throw new InvalidArgumentException("Can only use objects, integers or strings as map keys.");
    }

    public function first(callable $filter): mixed
    {
        foreach ($this->values as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function where(callable $filter): HashMap
    {
        $result = new HashMap();

        foreach ($this->values as $internalKey => $item) {
            if ($filter($item)) {
                $result->values[$internalKey] = $item;
            }
        }

        return $result;
    }

    public function hasKey(mixed $key, bool $safe = true): bool
    {
        $internalKey = $this->getHashForKey($key);

        return array_key_exists($internalKey, $this->values);
    }
}