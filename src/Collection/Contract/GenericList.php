<?php

namespace Philly\Base\Collection\Contract;

use Countable;

/**
 * @template T
 */
interface GenericList extends Countable
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
