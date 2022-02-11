<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateTime;
use Elephox\Cache\Contract\TempDirCacheConfiguration;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\Stream\StringStream;
use JetBrains\PhpStorm\Pure;
use Psr\Cache\CacheItemInterface;

class TempDirCache extends AbstractCache implements Contract\TempDirCache
{
	/**
	 * @var array<string, CacheItemInterface>
	 */
	private array $items = [];

	private int $changes = 0;

	public function __construct(private TempDirCacheConfiguration $configuration)
	{
		$this->load();
	}

	public function getConfiguration(): TempDirCacheConfiguration
	{
		return $this->configuration;
	}

	#[Pure]
	private function getCacheDir(): DirectoryContract
	{
		return new Directory($this->configuration->getTempDir());
	}

	#[Pure]
	private function getWriteBackThreshold(): int
	{
		return $this->configuration->getWriteBackThreshold();
	}

	#[Pure]
	private function getCacheId(): string
	{
		return $this->configuration->getCacheId();
	}

	public function getItems(array $keys = []): iterable
	{
		return array_filter(
			$this->items,
			static fn(string $key): bool => in_array($key, $keys, true),
			ARRAY_FILTER_USE_KEY
		);
	}

	public function getItem(string $key): CacheItemInterface
	{
		if ($this->hasItem($key)) {
			return $this->items[$key];
		}

		$expiresAt = $this->calculateExpiresAt(new DateTime());

		return new ImmutableCacheItem($key, null, false, $expiresAt);
	}

	public function hasItem(string $key): bool
	{
		return array_key_exists($key, $this->items);
	}

	public function clear(): bool
	{
		$this->items = [];

		$this->persist();

		return true;
	}

	public function deleteItem(string $key): bool
	{
		if (!$this->hasItem($key)) {
			return false;
		}

		$this->changes++;
		$this->checkWriteBackThreshold();

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
		$this->items[$item->getKey()] = $item;
		$this->persist();

		return true;
	}

	public function saveDeferred(CacheItemInterface $item): bool
	{
		$this->items[$item->getKey()] = $item;

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

	public function persist(): void
	{
		$this->getCacheDir()->ensureExists();

		$file = $this->getCacheDir()->getFile($this->getCacheId());
		$file->putContents(StringStream::from(serialize($this->items)));

		$classes = array_unique(
			array_merge(
				array_map(
					static fn (CacheItemInterface $item): string => get_debug_type($item->get()),
					$this->items
				),
				[ CacheItemInterface::class ],
			)
		);
		$classesStream = StringStream::from(serialize($classes));
		$classesFile = $this->getCacheDir()->getFile($this->getCacheId() . ".classes");
		$classesFile->putContents($classesStream);

		$this->changes = 0;
	}

	public function load(): void
	{
		if (!$this->getCacheDir()->exists()) {
			return;
		}

		$classesFile = $this->getCacheDir()->getFile($this->getCacheId() . ".classes");
		if (!$classesFile->exists()) {
			return;
		}

		/** @var list<class-string> */
		$classes = unserialize($classesFile->stream()->getContents(), ['allowed_classes' => false]);

		$file = $this->getCacheDir()->getFile($this->getCacheId());
		$contents = $file->stream()->getContents();

		/** @var array<string, CacheItemInterface> */
		$this->items = unserialize($contents, ['allowed_classes' => $classes]);

		$this->changes = 0;
	}
}
