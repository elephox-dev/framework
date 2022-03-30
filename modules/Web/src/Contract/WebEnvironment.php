<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\Configuration\Contract\Environment;
use Elephox\Files\Contract\Directory;

interface WebEnvironment extends Environment
{
	public function getWebRootDirectory(): Directory;
}
