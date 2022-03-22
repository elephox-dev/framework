<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\Http\Contract\Request;

interface Router
{
	public function getHandler(Request $request): RouteHandler;
}
