<?php

namespace Philly\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends \Philly\Collection\Contract\GenericCollection<TValue>
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
}
