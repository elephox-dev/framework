<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Stream\Contract\Stream;
use Elephox\Support\Contract\HasHash;
use Elephox\Support\Contract\MimeType;

interface File extends FilesystemNode, HasHash
{
	public function getExtension(): string;

	public function getSize(): int;

	public function getMimeType(): ?MimeType;

	public function getContents(bool $readable = true, bool $writeable = false, bool $create = false, bool $append = false, bool $truncate = false): Stream;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isExecutable(): bool;
}
