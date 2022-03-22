<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Closure;
use Elephox\Http\Contract\Request;
use Elephox\Http\Contract\ResponseBuilder;

interface WebMiddleware
{
	/**
	 * @param Request $request
	 * @param Closure(Request): ResponseBuilder $next
	 * @return ResponseBuilder
	 */
	public function handle(Request $request, Closure $next): ResponseBuilder;
}
