<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Directory;
use Elephox\Files\Path;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class TempDirCacheConfiguration extends AbstractCacheConfiguration
{
	/**
	 * @var non-empty-string $cacheId
	 */
	public readonly string $cacheId;

	public readonly DirectoryContract $tempDir;

	/**
	 * @param DateInterval|int|null $ttl
	 * @param non-empty-string|null $cacheId
	 * @param Directory|null $tempDir
	 * @param int $writeBackThreshold
	 */
	#[Pure]
	public function __construct(
		DateInterval|int|null $ttl = null,
		?string $cacheId = null,
		?Directory $tempDir = null,
		public readonly int $writeBackThreshold = 200,
	) {
		parent::__construct($ttl);

		if ($cacheId !== null) {
			$this->cacheId = $cacheId;
		} else {
			/**
			 * @var non-empty-string
			 */
			$this->cacheId = uniqid('', true);
		}

		if ($tempDir !== null) {
			$this->tempDir = $tempDir;
		} else {
			/** @psalm-suppress ImpureFunctionCall */
			$this->tempDir = new Directory(Path::join(sys_get_temp_dir(), 'elephox-cache'));
		}
	}
}
