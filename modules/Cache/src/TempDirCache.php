<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateTime;
use Elephox\Files\Contract\Directory as DirectoryContract;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;

class TempDirCache extends AbstractCache
{
	/**
	 * @var array<string, CacheItemInterface>
	 */
	private array $floatingItems = [];

	/**
	 * @var list<string>
	 */
	private array $persistedItemKeys = [];

	private int $changes = 0;

	public function __construct(private readonly TempDirCacheConfiguration $configuration)
	{
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
			return $this->floatingItems[$key];
		}

		$expiresAt = $this->calculateExpiresAt(new DateTime());

		return new ImmutableCacheItem($key, null, false, $expiresAt);
	}

	public function hasItem(string $key): bool
	{
		return in_array($key, $this->persistedItemKeys, true);
	}

	public function clear(): bool
	{
		$this->floatingItems = [];

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
		parent::deleteItems($keys);

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

		$file = $this->getCacheDir()->getFile($this->getCacheId());
		$classesFile = $this->getCacheDir()->getFile($this->getCacheId() . '.classes');

		if (empty($this->floatingItems)) {
			if ($file->exists()) {
				$file->delete();
			}

			if ($classesFile->exists()) {
				$classesFile->delete();
			}

			$this->changes = 0;
			$this->persistedItemKeys = [];

			return;
		}

		$file->putContents(serialize($this->floatingItems));

		$classes = array_unique(
			array_merge(
				array_values(
					array_map(
						static fn (CacheItemInterface $item): string => $item::class,
						$this->floatingItems,
					),
				),
				array_values(
					array_filter(
						array_map(
							static function (CacheItemInterface $item): ?string {
								$value = $item->get();

								if ($value !== null) {
									return $value::class;
								}

								return null;
							},
							$this->floatingItems,
						),
					),
				),
				[CacheItemInterface::class],
			),
		);
		$classesFile->putContents(serialize($classes));

		$this->changes = 0;
		$this->persistedItemKeys = array_keys($this->floatingItems);
	}

	protected function load(): void
	{
		if (!$this->getCacheDir()->exists()) {
			return;
		}

		$classesFile = $this->getCacheDir()->getFile($this->getCacheId() . '.classes');
		if (!$classesFile->exists()) {
			return;
		}

		/** @var list<class-string> */
		$classes = unserialize($classesFile->getContents(), ['allowed_classes' => false]);

		$file = $this->getCacheDir()->getFile($this->getCacheId());
		$contents = $file->getContents();

		/** @var array<string, CacheItemInterface> */
		$this->floatingItems = unserialize($contents, ['allowed_classes' => $classes]);
		$this->persistedItemKeys = array_keys($this->floatingItems);

		$this->changes = 0;
	}
}
