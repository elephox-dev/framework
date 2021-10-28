<?php

namespace Philly\Base\Collection;

use ArrayAccess;
use InvalidArgumentException;
use Philly\Base\Collection\Contract\GenericListContract;
use Philly\Base\Exception\InvalidOffsetException;

/**
 * @template T
 *
 * @template-implements GenericListContract<T>
 * @template-implements  ArrayAccess<int, T>
 */
class ArrayList implements GenericListContract, ArrayAccess
{
    /** @var array<int, T> */
    private array $list = [];

    /**
     * @param array<int, T> $items
     */
    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->add($item);
        }
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->list);
    }

    /**
     * @return T
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!is_int($offset)) {
            throw new InvalidArgumentException("Cannot use offset types other than int.");
        }

        return $this->get($offset);
    }

    /**
     * @param T $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);

            return;
        }

        if (!is_int($offset)) {
            throw new InvalidArgumentException("Cannot use offset types other than int.");
        }

        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->list[$offset]);
    }

    public function count(): int
    {
        return count($this->list);
    }

    public function set(int $index, mixed $value): void
    {
        $this->list[$index] = $value;
    }

    /**
     * @return T
     */
    public function get(int $index): mixed
    {
        if (!$this->offsetExists($index)) {
            throw new InvalidOffsetException("Offset $index does not exist.");
        }

        return $this->list[$index];
    }

    /**
     * @param T $value
     */
    public function add(mixed $value): void
    {
        $this->set($this->count(), $value);
    }
}
