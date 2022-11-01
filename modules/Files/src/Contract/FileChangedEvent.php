<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Events\Contract\Event;

interface FileChangedEvent extends Event
{
	public function file(): File;
}
