<?php

namespace Philly\Base\Collection\Contract;

use Countable;

/**
 * @template T
 *
 * @extends \Philly\Base\Collection\Contract\GenericCollection<T>
 */
interface ReadonlyList extends GenericCollection, Countable
{
    /**
     * @return T
     */
    public function get(int $index): mixed;

    /**
     * @param callable(T): bool $filter
     * @return GenericList<T>
     */
    public function where(callable $filter): GenericList;

    public function isEmpty(): bool;
}
