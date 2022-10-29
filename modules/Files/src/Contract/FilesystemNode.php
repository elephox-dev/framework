<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTimeInterface;
use Stringable;

interface FilesystemNode extends Stringable
{
	public function path(): string;

	public function relativePathTo(self $node): string;

	public function name(): string;

	public function modifiedAt(): DateTimeInterface;

	/**
	 * @param positive-int $levels
	 */
	public function parent(int $levels = 1): Directory;

	public function exists(): bool;
}
