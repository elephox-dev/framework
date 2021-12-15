<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Support\Contract\HasHash;
use Elephox\Support\Contract\MimeType;

interface File extends FilesystemNode, HasHash
{
	public function getExtension(): string;

	public function getSize(): int;

	public function getMimeType(): ?MimeType;

	public function moveTo(string $path): bool;

	public function exists(): bool;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isExecutable(): bool;
}
