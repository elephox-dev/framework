<?php
declare(strict_types=1);

namespace Elephox\Web;

use Elephox\Configuration\GlobalEnvironment;
use Elephox\Files\Directory;
use Elephox\Web\Contract\WebEnvironment;

class GlobalWebEnvironment extends GlobalEnvironment implements WebEnvironment
{
	public function getWebRoot(): Directory
	{
		return $this->getRoot()->getDirectory('public');
	}
}
