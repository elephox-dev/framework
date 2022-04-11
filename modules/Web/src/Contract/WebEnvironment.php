<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\Configuration\Contract\Environment;
use Elephox\Files\Contract\Directory;

/**
 * @property-read Directory $webRoot
 */
interface WebEnvironment extends Environment
{
	public function getWebRoot(): Directory;
}
