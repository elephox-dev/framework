<?php
declare(strict_types=1);

namespace Elephox\Console\Contract;

use Elephox\Configuration\Contract\Environment;
use Elephox\Files\Contract\Directory;

interface HostEnvironment extends Environment
{
	public function getRootDirectory(): Directory;
}
