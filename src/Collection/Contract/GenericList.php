<?php

namespace Philly\Base\Collection\Contract;

use Countable;

/**
 * @template T
 *
 * @template-implements \Philly\Base\Collection\Contract\GenericCollection<T>
 */
interface GenericList extends GenericCollection, Countable, Filterable
{
    /**
     * @param T $value
     */
    public function set(int $index, mixed $value): void;

    /**
     * @return T
     */
    public function get(int $index): mixed;

    /**
     * @param T $value
     */
    public function add(mixed $value): void;
}
