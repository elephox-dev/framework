<?php

namespace Philly\Base\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements \Philly\Base\Collection\Contract\GenericCollection<TValue>
 */
interface GenericMap extends GenericCollection, Filterable
{
    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function put(mixed $key, mixed $value): void;

    /**
     * @param TKey $key
     * @return TValue
     */
    public function get(mixed $key): mixed;

    /**
     * @param callable(TValue): bool $filter
     * @return GenericMap<TKey, TValue>
     */
    public function where(callable $filter): GenericMap;
}
