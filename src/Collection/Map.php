<?php

namespace Philly\Base\Collection;

use InvalidArgumentException;
use Philly\Base\Collection\Contract\GenericMap;
use Philly\Base\Exception\InvalidOffsetException;
use Philly\Base\Support\Contract\HashGenerator;
use Philly\Base\Support\SplObjectIdHashGenerator;
use WeakReference;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements GenericMap<TKey, TValue>
 */
class Map implements GenericMap
{
    /** @var array<array-key, TValue> */
    private array $values = [];

    /** @var array<array-key, WeakReference> */
    private array $keys = [];

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

        if (is_object($key)) {
            $this->keys[$mapKey] = WeakReference::create($key);
        }
    }

    public function get(mixed $key): mixed
    {
        $internalKey = $this->getHashForKey($key);

        $this->cleanupReference($internalKey, false);

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
        foreach ($this->values as $internalKey => $item) {
            $this->cleanupReference($internalKey, false);

            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function where(callable $filter): Map
    {
        $result = new Map();

        foreach ($this->values as $internalKey => $item) {
            $this->cleanupReference($internalKey, false);

            if ($filter($item)) {
                $result->values[$internalKey] = $item;
            }
        }

        return $result;
    }

    public function firstKey(callable $filter): mixed
    {
        foreach ($this->values as $internalKey => $item) {
            $reference = $this->keys[$internalKey];

            /** @var TKey|null $key */
            $key = $reference->get();
            if ($key === null) {
                $this->cleanupReference($internalKey, true);

                continue;
            }

            if ($filter($key, $item)) {
                return $key;
            }
        }

        return null;
    }

    public function whereKey(callable $filter): Map
    {
        $result = new Map();

        foreach ($this->values as $internalKey => $value) {
            $reference = $this->keys[$internalKey];

            /** @var TKey|null $key */
            $key = $reference->get();
            if ($key === null) {
                $this->cleanupReference($internalKey, true);

                continue;
            }

            if ($filter($key, $value)) {
                $result->values[$internalKey] = $value;
                $result->keys[$internalKey] = $reference;
            }
        }

        return $result;
    }

    /**
     * @param array-key $internalKey
     */
    private function cleanupReference(int|string $internalKey, bool $skipCheck): void
    {
        if (!array_key_exists($internalKey, $this->keys)) {
            return;
        }

        if ($skipCheck || $this->keys[$internalKey]->get() !== null) {
            return;
        }

        unset($this->keys[$internalKey], $this->values[$internalKey]);
    }

    private function cleanupReferences(): void
    {
        foreach (array_keys($this->keys) as $internalKey) {
            $this->cleanupReference($internalKey, false);
        }
    }

    public function hasKey(mixed $key, bool $safe = true): bool
    {
        $internalKey = $this->getHashForKey($key);

        if ($safe) {
            $this->cleanupReferences();
        }

        return array_key_exists($internalKey, $this->values);
    }
}
