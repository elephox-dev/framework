<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\Files\Contract\Directory;
use Elephox\Host\Contract\HostEnvironment;

interface WebHostEnvironment extends HostEnvironment
{
	public function getWebRootDirectory(): Directory;
}
