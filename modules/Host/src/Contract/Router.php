<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Http\Contract\Request;

interface Router
{
	public function getHandler(Request $request): RouteHandler;
}
