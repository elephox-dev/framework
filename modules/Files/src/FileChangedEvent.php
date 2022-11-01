<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Events\ClassNameAsEventName;
use Elephox\Events\StopsPropagation;
use Elephox\Files\Contract\File;

class FileChangedEvent implements Contract\FileChangedEvent
{
	use ClassNameAsEventName;
	use StopsPropagation;

	public function __construct(
		protected readonly File $file,
	) {
	}

	public function file(): File
	{
		return $this->file;
	}
}
