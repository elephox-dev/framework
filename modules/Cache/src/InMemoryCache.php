<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateTime;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

class InMemoryCache extends AbstractCache
{
	/**
	 * @var array<string, CacheItemInterface>
	 */
	private array $cache = [];

	/**
	 * @var array<string, CacheItemInterface>
	 */
	private array $deferred = [];

	#[Pure]
	public function __construct(private readonly InMemoryCacheConfiguration $configuration)
	{
	}

	public function getConfiguration(): InMemoryCacheConfiguration
	{
		return $this->configuration;
	}

	/**
	 * @throws InvalidArgumentException
	 *
	 * @param string $key
	 */
	public function getItem(string $key): CacheItemInterface
	{
		if ($this->hasItem($key)) {
			return $this->cache[$key];
		}

		$expiresAt = $this->calculateExpiresAt(new DateTime());

		return new ImmutableCacheItem($key, null, false, $expiresAt);
	}

	public function hasItem(string $key): bool
	{
		return isset($this->cache[$key]);
	}

	public function clear(): bool
	{
		$this->cache = [];

		gc_collect_cycles();

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
		foreach ($keys as $key) {
			$this->deleteItem($key);
		}

		return true;
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
		foreach ($this->deferred as $key => $item) {
			$this->cache[$key] = $item;
		}

		$this->deferred = [];

		gc_collect_cycles();

		return true;
	}
}
