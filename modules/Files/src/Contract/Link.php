<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

interface Link extends FilesystemNode
{
	public function target(): FilesystemNode;
}
