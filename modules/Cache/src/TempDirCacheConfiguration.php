<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use Elephox\Files\Path;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class TempDirCacheConfiguration extends AbstractCacheConfiguration implements Contract\TempDirCacheConfiguration
{
	/**
	 * @var non-empty-string $cacheId
	 */
	private string $cacheId;

	/**
	 * @var non-empty-string $tempDir
	 */
	private string $tempDir;

	/**
	 * @param DateInterval|int|null $ttl
	 * @param non-empty-string|null $cacheId
	 * @param non-empty-string|null $tempDir
	 * @param positive-int|0 $writeBackThreshold
	 */
	#[Pure]
	public function __construct(
		DateInterval|int|null $ttl = null,
		?string $cacheId = null,
		?string $tempDir = null,
		private int $writeBackThreshold = 200,
	)
	{
		parent::__construct($ttl);

		if ($cacheId !== null) {
			$this->cacheId = $cacheId;
		} else {
			/**
			 * @var non-empty-string
			 */
			$this->cacheId = md5(uniqid('', true));
		}

		if ($tempDir !== null) {
			$this->tempDir = $tempDir;
		} else {
			/**
			 * @psalm-suppress ImpureFunctionCall
			 * @var non-empty-string
			 */
			$this->tempDir = Path::join(sys_get_temp_dir(), "elephox-cache");
		}
	}

	#[Pure]
	public function getTempDir(): string
	{
		return $this->tempDir;
	}

	#[Pure]
	public function getWriteBackThreshold(): int
	{
		return $this->writeBackThreshold;
	}

	#[Pure]
	public function getCacheId(): string
	{
		return $this->cacheId;
	}
}
