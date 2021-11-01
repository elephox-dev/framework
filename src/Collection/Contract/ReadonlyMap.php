<?php

namespace Philly\Base\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends \Philly\Base\Collection\Contract\GenericCollection<TValue>
 */
interface ReadonlyMap extends GenericCollection
{
    /**
     * @param TKey $key
     * @return TValue
     */
    public function get(mixed $key): mixed;

    /**
     * @param TKey $key
     */
    public function hasKey(mixed $key): bool;

    /**
     * @param callable(TValue): bool $filter
     * @return GenericMap<TKey, TValue>
     */
    public function where(callable $filter): GenericMap;

    /**
     * @param callable(TKey, TValue): bool $filter
     * @return TKey|null
     */
    public function firstKey(callable $filter): mixed;

    /**
     * @param callable(TKey, TValue): bool $filter
     * @return ReadonlyMap<TKey, TValue>
     */
    public function whereKey(callable $filter): ReadonlyMap;
}
