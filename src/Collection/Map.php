<?php

namespace Philly\Base\Collection;

use InvalidArgumentException;
use Philly\Base\Collection\Contract\GenericMap;
use Philly\Base\Support\Contract\HashGenerator;
use Philly\Base\Support\SplObjectIdHashGenerator;

/**
 * @template TKey as string|int|object
 * @template TValue
 *
 * @template-implements GenericMap<TKey, TValue>
 */
class Map implements GenericMap
{
    /** @var array<array-key, TValue> */
    private array $map = [];

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

        $this->map[$mapKey] = $value;
    }

    public function get(mixed $key): mixed
    {
        $mapKey = $this->getHashForKey($key);

        return $this->map[$mapKey];
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
        foreach ($this->map as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function where(callable $filter): Map
    {
        $result = new Map();

        foreach ($this->map as $key => $item) {
            if ($filter($item)) {
                $result->map[$key] = $item;
            }
        }

        return $result;
    }
}
