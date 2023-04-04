<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Stringable;

interface FilesystemNode extends Stringable
{
	#[Pure]
	public function path(): string;

	#[Pure]
	public function relativePathTo(self $node): string;

	#[Pure]
	public function name(): string;

	public function modifiedAt(): DateTimeInterface;

	/**
	 * @param int<1, max> $levels
	 */
	public function parent(int $levels = 1): Directory;

	public function exists(): bool;

	public function delete(): void;
}
