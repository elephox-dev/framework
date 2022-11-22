<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateTime;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Files\Contract\Directory as DirectoryContract;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;

class TempDirCache extends AbstractCache
{
	/**
	 * @var ArrayMap<string, CacheItemInterface>
	 */
	private ArrayMap $floatingItems;

	/**
	 * @var ArrayList<string>
	 */
	private ArrayList $persistedItemKeys;

	private int $changes = 0;

	public function __construct(private readonly TempDirCacheConfiguration $configuration)
	{
		$this->floatingItems = new ArrayMap();
		$this->persistedItemKeys = new ArrayList();

		$this->load();
	}

	public function getConfiguration(): TempDirCacheConfiguration
	{
		return $this->configuration;
	}

	#[Pure]
	protected function getCacheDir(): DirectoryContract
	{
		return $this->getConfiguration()->tempDir;
	}

	#[Pure]
	protected function getWriteBackThreshold(): int
	{
		return $this->getConfiguration()->writeBackThreshold;
	}

	#[Pure]
	protected function getCacheId(): string
	{
		return $this->getConfiguration()->cacheId;
	}

	public function getItem(string $key): CacheItemInterface
	{
		if ($this->hasItem($key)) {
			return $this->floatingItems->get($key);
		}

		$expiresAt = $this->calculateExpiresAt(new DateTime());

		return new ImmutableCacheItem($key, null, false, $expiresAt);
	}

	public function hasItem(string $key): bool
	{
		return $this->persistedItemKeys->contains($key);
	}

	public function clear(): bool
	{
		$this->floatingItems->clear();

		$this->persist();

		return true;
	}

	public function deleteItem(string $key): bool
	{
		if (!$this->hasItem($key)) {
			return false;
		}

		unset($this->floatingItems[$key]);
		$this->persist();

		return true;
	}

	public function deleteItems(iterable $keys): bool
	{
		if (!parent::deleteItems($keys)) {
			return false;
		}

		$this->persist();

		return true;
	}

	public function save(CacheItemInterface $item): bool
	{
		$this->floatingItems[$item->getKey()] = $item;
		$this->persist();

		return true;
	}

	public function saveDeferred(CacheItemInterface $item): bool
	{
		$this->floatingItems[$item->getKey()] = $item;

		$this->changes++;
		$this->checkWriteBackThreshold();

		return true;
	}

	public function commit(): bool
	{
		$this->persist();

		return true;
	}

	private function checkWriteBackThreshold(): void
	{
		if ($this->getWriteBackThreshold() > 0 && $this->changes >= $this->getWriteBackThreshold()) {
			$this->persist();
		}
	}

	protected function persist(): void
	{
		$this->getCacheDir()->ensureExists();

		$itemsFile = $this->getCacheDir()->file($this->getCacheId() . '.phpo');
		$classesFile = $this->getCacheDir()->file($this->getCacheId() . '.classes.phpo');

		if ($this->floatingItems->isEmpty()) {
			// cache is empty, delete persisted contents

			if ($itemsFile->exists()) {
				$itemsFile->delete();
			}

			if ($classesFile->exists()) {
				$classesFile->delete();
			}

			$this->changes = 0;
			$this->persistedItemKeys->clear();

			return;
		}

		$itemsFile->writeContents(serialize($this->floatingItems->toArray()));

		/** @psalm-suppress InvalidArgument */
		$classes = $this->floatingItems
			->selectMany(static function (CacheItemInterface $item): array {
				$classes = [];
				$classes[] = $item::class;

				/** @var mixed $value */
				$value = $item->get();

				if ($value !== null) {
					$classes[] = $value::class;
				}

				return $classes;
			})
			->values()
			->append(CacheItemInterface::class)
			->unique()
			->toList()
		;
		$classesFile->writeContents(serialize($classes));

		$this->changes = 0;

		/** @var ArrayList<string> */
		$this->persistedItemKeys = $this->floatingItems->keys()->toArrayList();
	}

	protected function load(): void
	{
		if (!$this->getCacheDir()->exists()) {
			return;
		}

		$classesFile = $this->getCacheDir()->file($this->getCacheId() . '.classes.phpo');
		if (!$classesFile->exists()) {
			return;
		}

		/** @var list<class-string> */
		$classes = unserialize($classesFile->contents(), ['allowed_classes' => false]);

		$itemsFile = $this->getCacheDir()->file($this->getCacheId() . '.phpo');
		if (!$itemsFile->exists()) {
			return;
		}

		/** @var array<string, CacheItemInterface> $deserialized */
		$deserialized = unserialize($itemsFile->contents(), ['allowed_classes' => $classes]);
		$this->floatingItems = ArrayMap::from($deserialized);

		/** @var ArrayList<string> */
		$this->persistedItemKeys = $this->floatingItems->keys()->toArrayList();

		$this->changes = 0;
	}
}
