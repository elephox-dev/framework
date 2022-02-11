<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use DateTime;
use Elephox\Cache\Contract\InMemoryCacheConfiguration;
use Elephox\Collection\ArrayMap;
use Exception;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
use WeakReference;

class InMemoryCache extends AbstractCache implements Contract\InMemoryCache
{
	/**
	 * @var array<string, WeakReference<CacheItemInterface>>
	 */
	private array $cache = [];

	/**
	 * @var array<string, WeakReference<CacheItemInterface>>
	 */
	private array $deferred = [];

	#[Pure]
	public function __construct(private InMemoryCacheConfiguration $configuration) {
	}

	/**
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function getItem(string $key): CacheItemInterface
	{
		if ($this->hasItem($key)) {
			$item = $this->cache[$key];
			if ($item->get() !== null) {
				return $item->get();
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
		return isset($this->cache[$key]) && $this->cache[$key]->get() !== null;
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
		$anyDeleted = false;
		foreach ($keys as $key) {
			$anyDeleted = $this->deleteItem($key) || $anyDeleted;
		}

		return $anyDeleted;
	}

	public function save(CacheItemInterface $item): bool
	{
		$this->cache[$item->getKey()] = WeakReference::create($item);

		return true;
	}

	public function saveDeferred(CacheItemInterface $item): bool
	{
		$this->deferred[$item->getKey()] = WeakReference::create($item);

		return true;
	}

	public function commit(): bool
	{
		foreach ($this->deferred as $key => $item) {
			if ($item->get() !== null) {
				$this->cache[$key] = $item;
			}
		}

		$this->deferred = [];

		gc_collect_cycles();

		return true;
	}

	/**
	 * @return ArrayMap<string, CacheItemInterface>
	 *
	 * @throws InvalidArgumentException
	 */
	public function getItems(array $keys = []): ArrayMap
	{
		/**
		 * @var ArrayMap<string, CacheItemInterface>
		 */
		$map = new ArrayMap();

		foreach ($keys as $key) {
			if (!is_string($key)) {
				throw new \InvalidArgumentException("Only string keys are allowed!");
			}

			$map->put($key, $this->getItem($key));
		}

		return $map;
	}

	public function getConfiguration(): InMemoryCacheConfiguration
	{
		return $this->configuration;
	}
}
