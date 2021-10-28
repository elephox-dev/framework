<?php

namespace Philly\Base\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 */
interface GenericMapContract
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
}
