<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Files\Contract\Directory;

interface WebHostEnvironment extends HostEnvironment
{
	public function getWebRootDirectory(): Directory;
}
