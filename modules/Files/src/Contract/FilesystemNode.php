<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTime;

interface FilesystemNode
{
	public function getPath(): string;

	public function getName(): string;

	public function getModifiedTime(): DateTime;

	/**
	 * @param positive-int $levels
	 * @return Directory
	 */
	public function getParent(int $levels = 1): Directory;

	public function exists(): bool;
}
