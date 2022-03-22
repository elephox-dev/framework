<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Files\Directory;
use Elephox\Host\GlobalHostEnvironment;
use Elephox\Web\Contract\WebHostEnvironment;

class GlobalWebHostEnvironment extends GlobalHostEnvironment implements WebHostEnvironment
{
	public function getWebRootDirectory(): Directory
	{
		return $this->getRootDirectory()->getDirectory('public');
	}
}
