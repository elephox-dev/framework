<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTimeInterface;
use Stringable;

interface FilesystemNode extends Stringable
{
	public function getPath(): string;

	public function getName(): string;

	public function getModifiedTime(): DateTimeInterface;

	/**
	 * @param positive-int $levels
	 */
	public function getParent(int $levels = 1): Directory;

	public function exists(): bool;
}
