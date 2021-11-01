<?php

namespace Philly\Base\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends \Philly\Base\Collection\Contract\ReadonlyMap<TKey, TValue>
 */
interface GenericMap extends ReadonlyMap
{
    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function put(mixed $key, mixed $value): void;

    /**
     * @param callable(TKey, TValue): bool $filter
     * @return GenericMap<TKey, TValue>
     */
    public function whereKey(callable $filter): GenericMap;
}
