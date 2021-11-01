<?php
declare(strict_types=1);

namespace Philly\Collection\Contract;

/**
 * @template T
 */
interface Filterable
{
    /**
     * @param callable(T): bool $filter
     * @return T|null
     */
    public function first(callable $filter): mixed;

    /**
     * @param callable(T): bool $filter
     * @return GenericCollection<T>
     */
    public function where(callable $filter): GenericCollection;
}
