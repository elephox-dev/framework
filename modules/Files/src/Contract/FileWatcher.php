<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

interface FileWatcher
{
	/**
	 * @param callable(FileChangedEvent): void $callback
	 */
	public function add(callable $callback, File ...$files): void;

	public function remove(File $file): void;

	public function poll(): bool;
}
