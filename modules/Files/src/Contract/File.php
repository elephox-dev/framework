<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTime;
use Elephox\Support\Contract\MimeType;
use Elephox\Support\Contract\HasHash;

interface File extends FilesystemNode, HasHash
{
	public function getExtension(): string;

	public function getSize(): int;

	public function getMimeType(): ?MimeType;

	public function getModifiedTime(): DateTime;

	public function getContents(): string;
}
