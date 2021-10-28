<?php

namespace Philly\Base\Collection;

use InvalidArgumentException;
use Philly\Base\Collection\Contract\GenericMapContract;
use Philly\Base\Support\Contract\HashGeneratorContract;
use Philly\Base\Support\SplObjectIdHashGenerator;

/**
 * @template TKey as string|int|object
 * @template TValue
 *
 * @template-implements GenericMapContract<TKey, TValue>
 */
class Map implements GenericMapContract
{
    /** @var array<array-key, TValue> */
    private array $map = [];

    private HashGeneratorContract $hashGenerator;

    /**
     * @param iterable<TKey, TValue> $items
     */
    public function __construct(iterable $items = [], ?HashGeneratorContract $hashGenerator = null)
    {
        $this->hashGenerator = $hashGenerator ?? new SplObjectIdHashGenerator();

        foreach ($items as $key => $value) {
            $this->put($key, $value);
        }
    }

    public function put(mixed $key, mixed $value): void
    {
        $mapKey = $this->getHashForKey($key);

        $this->map[$mapKey] = $value;
    }

    public function get(mixed $key): mixed
    {
        $mapKey = $this->getHashForKey($key);

        return $this->map[$mapKey];
    }

    /**
     * @param TKey $key
     * @return array-key
     */
    private function getHashForKey(mixed $key): int|string
    {
        if (is_int($key) || is_string($key)) {
            return $key;
        }

        if (is_object($key)) {
            return $this->hashGenerator->generateHash($key);
        }

        throw new InvalidArgumentException("Can only use objects, integers or strings as map keys.");
    }
}
