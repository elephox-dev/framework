<?php
declare(strict_types=1);

namespace Elephox\Cache;

use ArrayAccess;
use DateInterval;
use DateTime;
use Elephox\Cache\Contract\InMemoryCacheConfiguration;
use Elephox\Cache\Contract\InMemoryPool;
use Elephox\Collection\ArrayMap;
use Exception;
use LogicException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

class InMemoryCache implements InMemoryPool
{
	/**
	 * @returns ArrayAccess<string, CacheItemInterface>|array<string, CacheItemInterface>
	 */
	private static function getImplementation(InMemoryCacheConfiguration $configuration): ArrayAccess|array
	{
		$implementation = $configuration->getCacheImplementation();
		return match ($implementation) {
			'array' => [],
			default => new $implementation(),
		};
	}

	/**
	 * @var ArrayAccess<string, CacheItemInterface>|array<string, CacheItemInterface>
	 */
	private ArrayAccess|array $cache;

	/**
	 * @var ArrayAccess<string, CacheItemInterface>|array<string, CacheItemInterface>
	 */
	private ArrayAccess|array $deferred;

	public function __construct(private InMemoryCacheConfiguration $configuration) {
		$this->cache = self::getImplementation($configuration);
		$this->deferred = self::getImplementation($configuration);
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function getItem(string $key): CacheItemInterface
	{
		if ($this->hasItem($key)) {
			$item = $this->cache[$key];
			if ($item !== null) {
				return $item;
			}
		}

		$ttl = $this->configuration->getDefaultTTL();
		if ($ttl !== null) {
			$expiresAt = new DateTime();
			if (is_int($ttl)) {
				try {
					$expiresAt->add(new DateInterval('PT' . $ttl . 'S'));
				} catch (Exception $e) {
					throw new InvalidTtlException($ttl, previous: $e);
				}
			} else {
				$expiresAt->add($ttl);
			}
		} else {
			$expiresAt = null;
		}

		return new ImmutableCacheItem($key, null, false, $expiresAt);
	}

	public function hasItem(string $key): bool
	{
		return isset($this->cache[$key]);
	}

	public function clear(): bool
	{
		$this->cache = self::getImplementation($this->configuration);

		return true;
	}

	public function deleteItem(string $key): bool
	{
		if (!$this->hasItem($key)) {
			return false;
		}

		unset($this->cache[$key]);

		return true;
	}

	public function deleteItems(array $keys): bool
	{
		$anyDeleted = false;
		foreach ($keys as $key) {
			$anyDeleted = $this->deleteItem($key) || $anyDeleted;
		}

		return $anyDeleted;
	}

	public function save(CacheItemInterface $item): bool
	{
		$this->cache[$item->getKey()] = $item;

		return true;
	}

	public function saveDeferred(CacheItemInterface $item): bool
	{
		$this->deferred[$item->getKey()] = $item;

		return true;
	}

	public function commit(): bool
	{
		if (!is_iterable($this->deferred)) {
			throw new LogicException('Cannot commit deferred items: the cache implementation does not support iteration.');
		}

		foreach ($this->deferred as $key => $item) {
			$this->cache[$key] = $item;
		}

		$this->deferred = self::getImplementation($this->configuration);

		return true;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		return $this->hasItem($offset);
	}

	/**
	 * @throws InvalidKeyTypeException
	 */
	public function offsetGet(mixed $offset): CacheItemInterface
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		return $this->getItem($offset);
	}

	/**
	 * @throws InvalidValueTypeException
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset !== null) {
			throw new LogicException('Cannot add cache item with a non-null key to an InMemoryCache');
		}

		if (!$value instanceof CacheItemInterface) {
			throw new InvalidValueTypeException($value);
		}

		$this->save($value);
	}

	/**
	 * @throws InvalidKeyTypeException
	 * @throws InvalidArgumentException
	 */
	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		$this->deleteItem($offset);
	}

	/**
	 * @return iterable<string, CacheItemInterface>
	 *
	 * @throws InvalidArgumentException
	 */
	public function getItems(array $keys = []): iterable
	{
		$map = new ArrayMap();

		foreach ($keys as $key) {
			$map->put($key, $this->getItem($key));
		}

		return $map;
	}

	public function getConfiguration(): InMemoryCacheConfiguration
	{
		return $this->configuration;
	}
}
