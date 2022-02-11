<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

interface TempDirCache extends Cache
{
	public function getConfiguration(): TempDirCacheConfiguration;

	public function load(): void;

	public function persist(): void;
}
