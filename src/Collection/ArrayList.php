<?php

namespace Philly\Base\Collection;

use ArrayAccess;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Philly\Base\Collection\Contract\GenericList;
use Philly\Base\Exception\InvalidOffsetException;

/**
 * @template T
 *
 * @template-implements GenericList<T>
 * @template-implements  ArrayAccess<int, T>
 */
class ArrayList implements GenericList, ArrayAccess
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

    #[Pure] public function count(): int
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

    public function first(callable $filter): mixed
    {
        foreach ($this->list as $item) {
            if ($filter($item)) {
                return $item;
            }
        }

        return null;
    }

    public function where(callable $filter): ArrayList
    {
        $result = new ArrayList();

        foreach ($this->list as $item) {
            if ($filter($item)) {
                $result->add($item);
            }
        }

        return $result;
    }

    #[Pure] public function isEmpty(): bool
    {
        return empty($this->list);
    }
}
