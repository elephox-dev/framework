<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTime;

interface FilesystemNode
{
	public function getPath(): string;

	public function getName(): string;

	public function getModifiedTime(): DateTime;

	public function getParent(int $levels = 1): Directory;
}
