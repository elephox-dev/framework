<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use DateTime;
use Elephox\Http\Contract\MimeType;
use Elephox\Support\Contract\HasHash;

interface File extends HasHash
{
	public function getPath(): string;

	public function getName(): string;

	public function getExtension(): string;

	public function getSize(): int;

	public function getMimeType(): ?MimeType;

	public function getModifiedTime(): DateTime;

	public function getCreatedTime(): DateTime;

	public function getContents(): string;
}
