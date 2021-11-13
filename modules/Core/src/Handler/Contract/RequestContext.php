<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

use Elephox\Http\Contract\Request;

interface RequestContext extends Context
{
	public function getRequest(): Request;
}
