<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Files\Directory;
use Elephox\Host\Contract\HostEnvironment;
use Elephox\Host\Contract\WebHostEnvironment;
use RuntimeException;

class GlobalWebHostEnvironment extends GlobalHostEnvironment implements WebHostEnvironment
{
	public function getWebRootDirectory(): Directory
	{
		return $this->getRootDirectory()->getDirectory('public');
	}
}
