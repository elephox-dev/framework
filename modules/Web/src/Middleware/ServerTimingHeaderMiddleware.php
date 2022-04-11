<?php
declare(strict_types=1);

namespace Elephox\Web\Middleware;

use Closure;
use Elephox\Http\Contract\Request as RequestContract;
use Elephox\Http\Contract\ResponseBuilder as ResponseBuilderContract;
use Elephox\Web\Contract\WebMiddleware;

class ServerTimingHeaderMiddleware implements WebMiddleware
{
	public function __construct(
		private readonly string $name,
	) {
	}

	public function handle(RequestContract $request, Closure $next): ResponseBuilderContract
	{
		$timer = -hrtime(true);
		$responseBuilder = $next($request);
		$timer += hrtime(true);

		$responseBuilder->header('Server-Timing', $this->name . ';dur=' . ($timer / 1e+6)); // nanoseconds to milliseconds

		return $responseBuilder;
	}
}
